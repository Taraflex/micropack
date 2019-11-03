#!/usr/bin/env node

import { parse, Namespace, Field, Type, ReflectionObject } from 'protobufjs';
import { readFileSync, writeFileSync } from 'fs';
import { sync as fg } from 'fast-glob';
import groupBy = require('lodash.groupby');
import { resolve } from 'path';

const { argv } = require('yargs').option('out', {
    alias: 'o',
    type: 'string',
    description: '*.php output directory'
}).option('namespace', {
    alias: 'n',
    type: 'string',
    description: 'custom namespace'
}).option('php5', {
    type: 'boolean',
    description: 'php5 compability'
}).option('strip-namespace', {
    alias: 's',
    type: 'boolean',
    description: 'disable wrap code in namespace *{ }'
});

function* collectTypes(r: { nestedArray: ReflectionObject[] }) {
    for (let e of r.nestedArray) {
        if (e instanceof Type) {
            yield e;
        } else if (e instanceof Namespace) {
            yield* collectTypes(e);
        }
    }
}

const TypesDefault = {
    bool: 'false',
    uint32: '0',
    string: "''",
}

function getDef(f: Field) {
    return f.repeated ? 'array()' : TypesDefault[f.type];
}

function stringToJsdoc(description: string, pad: string = '') {
    return description && pad + '/**\n' + description.trim().split('\n').map(str => pad + ' * ' + str).join('\n') + `\n${pad} */`;
}

function realType(f: Field) {
    return f.type.replace('uint32', 'int');
}

function formatDocType(f: Field) {
    return realType(f) + (f.repeated ? '[]' : '');
}

function formatClassField(f: Field) {
    return stringToJsdoc(`@var ${formatDocType(f)} ` + (f.comment || ''), '        ') + '\n        public $' + f.name + ' = ' + getDef(f) + ';';
}

function fnDecl(f: Field) {
    return `${f.name}(${f.repeated ? 'array $v' : (argv.php5 ? '$v' : realType(f) + ' $v')})`;
}

function wrapNS(ns: string, code: string) {
    return argv.s ? code : `namespace ${ns} {
${code}
}`;
}

class Serializers {
    static bool(f: Field) {
        return f.repeated ? `
                $size = count($v);
                $this->__data[0] .= 'CVC' . $size;
                $this->__data[] = ${f.id};
                $this->__data[] = $size;
                $this->__data   = array_merge($this->__data, $v);
            ` : `
                $this->__data[0] .= 'C';
                $this->__data[] = ${f.id};
            `;
    }
    static uint32(f: Field) {
        return f.repeated ? `
                $size = count($v);
                $this->__data[0] .= 'CVV' . $size;
                $this->__data[] = ${f.id};
                $this->__data[] = $size;
                $this->__data   = array_merge($this->__data, $v);
            ` : `
                $this->__data[0] .= 'CV';
                $this->__data[] = ${f.id};
                $this->__data[] = $v;
            `;
    }
    static string(f: Field) {
        return f.repeated ? `
                $c = count($v);
                $this->__data[0] .= 'CV';
                $this->__data[] = ${f.id};
                $this->__data[] = $c;
                foreach ($v as $str) {
                    $size = strlen($str);
                    $this->__data[0] .= 'Va' . $size;
                    $this->__data[] = $size;
                    $this->__data[] = $str;
                }
            ` : `
                $size = strlen($v);
                $this->__data[0] .= 'CVa' . $size;
                $this->__data[] = ${f.id};
                $this->__data[] = $size;
                $this->__data[] = $v;
            `;
    }
}

const unpackUInt = `ord($data[$offset]) | (ord($data[++$offset]) << 8) | (ord($data[++$offset]) << 16) | (ord($data[($offset += 2) - 1]) << 24)`;

class Parsers {
    static bool(repeated: boolean) {
        return repeated ? `
                    $size = ${unpackUInt};
                    $this->{$field} = unpack('C' . $size, substr($data, $offset, $size));
                    $offset += $size;` : `
                    $this->{$field} = true;`;
    }
    static uint32(repeated: boolean) {
        return repeated ? `
                    $size = ${unpackUInt};
                    $this->{$field} = unpack('V' . $size, substr($data, $offset, $size * 4));
                    $offset += $size * 4;` : `
                    $this->{$field} = ${unpackUInt};`;
    }
    static string(repeated: boolean) {
        return repeated ? `
                    $count = ${unpackUInt};
                    while (--$count >= 0) {
                        $size = ${unpackUInt};
                        list(, $this->{$field}[]) = unpack('a' . $size, substr($data, $offset, $size));
                        $offset += $size;
                    }` : `
                    $size = ${unpackUInt};
                    list(, $this->{$field}) = unpack('a' . $size, substr($data, $offset, $size));
                    $offset += $size;`;
    }
}

const RESERVED_FIELDS = new Set(['create', 'dump', 'toArray', '__data', '__parse', '__indices', '__construct', '__destruct', '__call', '__callStatic', '__get', '__set', '__isset', '__unset', '__sleep', '__wakeup', '__toString', '__invoke', '__set_state', '__clone', '__debugInfo']);

const outDir = argv.o;
const protoFiles = fg((argv._ as string[]).map(s => s.replace(/\\/g, '/')), { absolute: true });
const namespace = (argv.n || '').replace(/\./g, '\\');

for (let proto of protoFiles) {
    for (let _t of collectTypes(parse(readFileSync(proto, 'utf8'), { keepCase: true }).root)) {
        const t: Type = _t;
        const fields: Field[] = t.fieldsArray;

        const serializerProps = fields.map(f => {
            if (!f.optional) {
                throw new Error(`Field: '${f.fullName}' must be optional`);
            }
            if (RESERVED_FIELDS.has(f.name)) {
                throw new Error(`Field: '${f.fullName}' has reserved name`);
            }
            if (!(f.type in Serializers)) {
                throw new Error(`Unsupported type ${f.type} on ${f.fullName}`);
            }
            return stringToJsdoc((f.comment || '') + `

@param  ${formatDocType(f)} $v
@return self`, '        ') + `
        public function ${fnDecl(f)}
        {
            if ($v) {${Serializers[f.type](f)}}
            return $this;
        }`;
        });

        const ns = namespace || t.fullName.slice(1, t.fullName.length - 1 - t.name.length).replace(/\./g, '\\');

        const serializerClass = wrapNS(ns, `
    final class ${t.name}Serializer
    {
        private $__data = array('');

        private function __construct()
        {}

        /**
         * @return string
         */
        public function dump()
        {
            return call_user_func_array('pack', $this->__data);
        }

        /**
         * @return string
         */
        public function __toString()
        {
            return $this->dump();
        }

        /**
         * @return self
         */
        public static function create()
        {
            return new static();
        }

${serializerProps.join('\n')}
    }`);

        const mainClass = wrapNS(ns, `
${t.comment ? stringToJsdoc(t.comment, '    ') : ''}
    final class ${t.name}
    {
${fields.map(formatClassField).join('\n')}

        private $__indices = array(${ fields.map(f => f.id + " => '" + f.name + "'").join(', ')});

        /**
         * @param string $data
         */
        public function __construct($data = null)
        {
            if ($data) {
                $size   = strlen($data);
                $offset = 0;
                do {
                    $offset = $this->__parse($data, $offset + 1, ord($data[$offset]));
                } while ($offset < $size);
            }
        }

        private function __parse($data, $offset, $id)
        {
            $field = $this->__indices[$id];
            switch ($id) {
${Object.values(groupBy(fields, (f: Field) => f.type + '|' + f.repeated)).map((g: Field[]) => `
                ${g.map(f => `case ${f.id}/*${f.name}*/:`).join(' ')}
                    // ${g[0].repeated ? 'repeated ' : ''}${g[0].type}${Parsers[g[0].type](g[0].repeated)}
                    return $offset;`).join('\n')}
            }
        }

        /**
         * @return string
         */
        public function dump()
        {
            return ${t.name}Serializer::create()
${fields.map(f => '                ->' + f.name + '($this->' + f.name + ')').join('\n')}->dump();
        }

        /**
         * @return string
         */
        public function __toString()
        {
            return $this->dump();
        }

        public function toArray()
        {
            return array(
${fields.map(f => `                '${f.name}' => $this->${f.name}`).join(',\n')}
            );
        }
    }`);
        if (outDir) {
            writeFileSync(resolve(outDir, t.name + 'Serializer.php'), '<?php\n\n' + serializerClass);
            writeFileSync(resolve(outDir, t.name + '.php'), '<?php\n\n' + mainClass);
        } else {
            console.log(serializerClass);
            console.log(mainClass)
        }
    }
}
