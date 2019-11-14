#!/usr/bin/env node

import { parse, Namespace, Enum, Field, Type, ReflectionObject } from 'protobufjs';
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
}).option('only-serializers', {
    type: 'boolean',
    description: 'generate serializers only'
});

const outDir: string = argv.o;
const protoFiles: string[] = fg((argv._ as string[]).map(s => s.replace(/\\/g, '/')), { absolute: true });
const namespace: string = trimSlashes((argv.n || '').replace(/\./g, '\\'));

function* collectTypes(r: { nestedArray: ReflectionObject[] }) {
    for (let e of r.nestedArray) {
        if (e instanceof Type || e instanceof Enum) {
            yield e;
        }
        //@ts-ignore
        if (e.nestedArray) {
            //@ts-ignore
            yield* collectTypes(e);
        }
    }
}

const TypesDefault = Object.assign(Object.create(null), {
    bool: 'false',
    uint32: '0',
    string: "''",
    bytes: "''"
})

function getDef(f: Field) {
    return f.repeated ? 'array()' : TypesDefault[f.type] || (f.type in types ? 'null' : '0');
}

function stringToJsdoc(description: string, pad: string = '') {
    return description && pad + '/**\n' + description.trim().split('\n').map(str => pad + ' * ' + str).join('\n') + `\n${pad} */`;
}

function formatCustomType(name: string, stripNs = argv.s) {
    if (stripNs) {
        const a = name.split('\\');
        return a[a.length - 1];
    }
    if (namespace) {
        return `\\${namespace}\\${formatCustomType(name, true)}`;
    }
    return name;
}

function realType(f: Field) {
    return TypesDefault[f.type] ? f.type.replace('uint32', 'int').replace('bytes', 'string') : (f.type in types ? formatCustomType(f.type) : 'int');
}

function formatDocType(f: Field) {
    return realType(f) + (f.repeated ? '[]' : '');
}

function formatClassField(f: Field) {
    return stringToJsdoc(`@var ${formatDocType(f)} ` + (f.comment || ''), '        ') + '\n        public $' + f.name + ' = ' + getDef(f) + ';';
}

function fnDecl(f: Field) {
    let param = f.repeated ? 'array $v' : (argv.php5 ? '$v' : realType(f) + ' $v');
    if (!f.repeated && (f.type in types)) {
        param += ' = null';
    }
    return `${f.name}(${param})`;
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
    static bytes(f: Field) {
        return Serializers.string(f);
    }
}

function customSerializer(f: Field) {
    return f.repeated ? `
                $c = count($v);
                $this->__data[0] .= 'CV';
                $this->__data[] = ${f.id};
                $this->__data[] = $c;
                foreach ($v as $str) {
                    $str  = (string) $str;
                    $size = strlen($str);
                    $this->__data[0] .= 'Va' . $size;
                    $this->__data[] = $size;
                    $this->__data[] = $str;
                }
            ` : `
                $v = (string) $v;` + Serializers.string(f);
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
                        $this->{$field}[] = substr($data, $offset, $size);
                        $offset += $size;
                    }` : `
                    $size = ${unpackUInt};
                    $this->{$field} = substr($data, $offset, $size);
                    $offset += $size;`;
    }
    static bytes(repeated: boolean) {
        return Parsers.string(repeated);
    }
}

function customParser(repeated: boolean) {
    return repeated ? `
                    $count = ${unpackUInt};
                    while (--$count >= 0) {
                        $size = ${unpackUInt};
                        $this->{$field}[] = new ${this}($data, $offset, $size);
                        $offset += $size;
                    }` : `
                    $size = ${unpackUInt};
                    $this->{$field} = new ${this}($data, $offset, $size);
                    $offset += $size;`;
}

const RESERVED_SERIALIZER_FIELDS = new Set(['create', 'dump', 'toArray', '__data', '__parse', '__indices', '__construct', '__destruct', '__call', '__callStatic', '__get', '__set', '__isset', '__unset', '__sleep', '__wakeup', '__toString', '__invoke', '__set_state', '__clone', '__debugInfo']);
const RESERVED_ENUM_FIELDS = new Set(['nameOf', '__construct', '__destruct', '__call', '__callStatic', '__get', '__set', '__isset', '__unset', '__sleep', '__wakeup', '__toString', '__invoke', '__set_state', '__clone', '__debugInfo']);

function isValidEnumField(e: Enum, f: string) {
    if (RESERVED_ENUM_FIELDS.has(f)) {
        throw new Error(`Field: '${e.fullName}.${f}' has reserved name`);
    }
}

function* pNs(t: ReflectionObject) {
    if (t) {
        if (t.constructor === Namespace) {
            yield t.name;
        }
        yield* pNs(t.parent)
    }
}

function trNs(t: Type | Enum) {
    let n = Array.from(pNs(t.parent)).filter(Boolean).join('\\');
    return n ? `\\${n}` : '';
}

function trName(t: Type | Enum) {
    return trNs(t) + '\\' + t.name;
}

function trimSlashes(s: string) {
    return s.replace(/^[\s\\]+/g, '').replace(/[\s\\]+$/g, '');
}

function wrapNS(t: Type | Enum, code: string) {
    return argv.s ? code : `namespace ${namespace || trimSlashes(trNs(t))} {
${code}
}`;
}

const types: Record<string, Type> = Object.create(null);
const enums: Record<string, Enum> = Object.create(null);

for (let proto of protoFiles) {
    let t: Type | Enum;
    for (t of collectTypes(parse(readFileSync(proto, 'utf8'), { keepCase: true }).root)) {
        if (t instanceof Enum) {
            const name = trName(t);
            Serializers[name] = Serializers.uint32;
            Parsers[name] = Parsers.uint32;
            enums[name] = t;
        } else if (t instanceof Type) {
            const name = trName(t);
            Serializers[name] = customSerializer;
            Parsers[name] = customParser.bind(name);
            types[name] = t;
        }
    }
}

if (!argv.onlySerializers) {
    for (let fullName in enums) {
        const t = enums[fullName];
        const enumClass = wrapNS(t, `
${t.comment ? stringToJsdoc(t.comment, '    ') : ''}
    final class ${t.name}
    {
${Object.keys(t.values).reduce((acc, k) => (isValidEnumField(t, k), acc.concat(stringToJsdoc(t.comments[k], '        '), `        const ${k} = ` + t.values[k] + ';')), []).filter(Boolean).join('\n')}

        /**
         * @param  int $v
         * @return string
         */
        public static function ${fnDecl(new Field('nameOf', 0, 'uint32'))}
        {
            switch ($v) {
${Object.keys(t.values).map(k => `                case ${t.values[k]}: return '${k}';`).join('\n')}
            }
            return '';
        }
    }`);
        if (outDir) {
            writeFileSync(resolve(outDir, t.name + '.php'), '<?php\n\n' + enumClass);
        } else {
            console.log(enumClass);
        }
    }
}

for (let fullName in types) {
    const t = types[fullName];
    const fields = t.fieldsArray;
    const ns = trNs(t);

    const serializerProps = fields.map(f => {
        if (!f.optional) {
            throw new Error(`Field: '${f.fullName}' must be optional`);
        }
        if (RESERVED_SERIALIZER_FIELDS.has(f.name)) {
            throw new Error(`Field: '${f.fullName}' has reserved name`);
        }
        if (!(f.type in TypesDefault)) {
            //todo better type resolve
            f.type = ns + '\\' + f.type;
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

    const serializerClass = wrapNS(t, `
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

    const mainClass = wrapNS(t, `
${t.comment ? stringToJsdoc(t.comment, '    ') : ''}
    class ${t.name}
    {
${fields.map(formatClassField).join('\n')}

        private $__indices = array(${ fields.map(f => f.id + " => '" + f.name + "'").join(', ')});

        /**
         * @param string $data
         * @param int    $offset
         * @param int    $size
         */
        public function __construct(${argv.php5 ? '$data = null' : 'string $data = null'}, ${argv.php5 ? '' : 'int '}$offset = 0, ${argv.php5 ? '' : 'int '}$size = 2147483647)
        {
            if ($data) {
                $end = $offset + $size;
                while ($offset < $end && $id = ord(@$data[$offset])) {
                    $offset = $this->__parse($data, $offset + 1, $id);
                }
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
            return ${formatCustomType(trName(t))}Serializer::create()
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
        if (!argv.onlySerializers) {
            writeFileSync(resolve(outDir, t.name + '.php'), '<?php\n\n' + mainClass);
        }
    } else {
        console.log(serializerClass);
        if (!argv.onlySerializers) {
            console.log(mainClass)
        }
    }
}
