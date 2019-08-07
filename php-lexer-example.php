<?php
use Phplrt\Lexer\Lexer;
use Phplrt\Source\File;

require __DIR__ . '/vendor/autoload.php';

$lexer = new class extends Lexer
{
    protected $states = [
        1 => [
            0 => [
                256 => '<(\?|%)=',
                257 => '(<\?php|<\?|<%)\s?',
                258 => '.+?',
                255 => '\xef\xbb\xbf',
            ],
            1 => [
                0 => 255,
            ],
            2 => [
                256 => 0,
                257 => 0,
            ],
            3 => [
                256 => 1,
                257 => 1,
            ],
        ],
        0 => [
            0 => [
                259 => '(//|#)[^\\n]*\n',
                260 => '/\\*.*?\\*/',
                261 => '(\\xfe\\xff|\\x20|\\x09|\\x0a|\\x0d)+',
                262 => '<<<\h*(\w+)[\s\S]*?\n\h*\g{-1}',
                263 => '<<<\h*\'(\w+)\'[\s\S]*?\n\h*\g{-1}',
                264 => '"([^"\\\\]*(?:\\\\.[^"\\\\]*)*)"',
                265 => '\'([^\'\\\\]*(?:\\\\.[^\'\\\\]*)*)\'',
                266 => '\((real|double|float|bool|boolean|array|string|unset|object|int)\)',
                267 => '0x[0-9a-fA-F]+(?:[eE][\\+\\-]?[0-9]+)?',
                268 => '0[0-9]+(?:[eE][\\+\\-]?[0-9]+)?',
                269 => '([0-9]*\\.[0-9]+|[0-9]+\\.[0-9]*)(?:[eE][\\+\\-]?[0-9]+)?',
                270 => '(?:0|[1-9][0-9]*)(?:[eE][\\+\\-]?[0-9]+)?',
                271 => '\$[a-zA-Z_\\x7f-\\xff][a-zA-Z0-9_\\x7f-\\xff]*\b',
                272 => '\\\\',
                273 => '\->',
                274 => '=>',
                275 => '\+\+',
                276 => '\-\-',
                277 => '\?\?',
                278 => '\*\*',
                279 => '@',
                280 => '\+=',
                281 => '\-=',
                282 => '\*=',
                283 => '/=',
                284 => '\.=',
                285 => '%=',
                286 => '&=',
                287 => '\|=',
                288 => '\^=',
                289 => '<<=',
                290 => '>>=',
                291 => '===',
                292 => '!==',
                293 => '==',
                294 => '!=',
                295 => '!',
                296 => '=',
                297 => '&&',
                298 => '\|\|',
                299 => '&',
                300 => '\|',
                301 => '~',
                302 => '\.\.\.',
                303 => '\-',
                304 => '\+',
                305 => '\*',
                306 => '\.',
                307 => '/',
                308 => '%',
                309 => '\^',
                310 => '<=>',
                311 => '<<',
                312 => '>>',
                313 => '>=',
                314 => '>',
                315 => '<=',
                316 => '<',
                317 => ';',
                318 => '::',
                319 => ':',
                320 => '\?',
                321 => ',',
                322 => 'or\b',
                323 => 'and\b',
                324 => 'xor\b',
                325 => '\\(',
                326 => '\\)',
                327 => '\\[',
                328 => '\\]',
                329 => '{',
                330 => '}',
                331 => 'new\b',
                332 => 'clone\b',
                333 => 'exit\b',
                334 => 'if\b',
                335 => 'elseif\b',
                336 => 'else\b',
                337 => 'endif\b',
                338 => 'echo\b',
                339 => 'do\b',
                340 => 'while\b',
                341 => 'endwhile\b',
                342 => 'for\b',
                343 => 'endfor\b',
                344 => 'foreach\b',
                345 => 'endforeach\b',
                346 => 'declare\b',
                347 => 'enddeclare\b',
                348 => 'as\b',
                349 => 'switch\b',
                350 => 'endswitch\b',
                351 => 'case\b',
                352 => 'default\b',
                353 => 'break\b',
                354 => 'continue\b',
                355 => 'goto\b',
                356 => 'function\b',
                357 => 'const\b',
                358 => 'return\b',
                359 => 'try\b',
                360 => 'catch\b',
                361 => 'finally\b',
                362 => 'throw\b',
                363 => 'use\b',
                364 => 'insteadof\b',
                365 => 'global\b',
                366 => 'static\b',
                367 => 'abstract\b',
                368 => 'final\b',
                369 => 'private\b',
                370 => 'protected\b',
                371 => 'public\b',
                372 => 'var\b',
                373 => 'unset\b',
                374 => 'isset\b',
                375 => 'empty\b',
                376 => '__halt_compiler\b',
                377 => 'class\b',
                378 => 'trait\b',
                379 => 'interface\b',
                380 => 'extends\b',
                381 => 'implements\b',
                382 => 'list\b',
                383 => 'array\b',
                384 => 'callable\b',
                385 => '__LINE__\b',
                386 => '__FILE__\b',
                387 => '__DIR__\b',
                388 => '__CLASS__\b',
                389 => '__TRAIT__\b',
                390 => '__METHOD__\b',
                391 => '__FUNCTION__\b',
                392 => '__NAMESPACE__\b',
                393 => 'true\b',
                394 => 'false\b',
                395 => 'null\b',
                396 => 'namespace\b',
                397 => 'self\b',
                398 => 'instanceof\b',
                399 => 'parent\b',
                400 => 'yield\s+from\b',
                401 => 'yield\b',
                402 => 'die\b',
                403 => 'print\b',
                404 => 'eval\b',
                405 => 'include\b',
                406 => 'include_once\b',
                407 => 'require\b',
                408 => 'require_once\b',
                409 => '[a-zA-Z_\\x7f-\\xff][a-zA-Z0-9_\\x7f-\\xff]*',
                410 => '\?>|%>',
                255 => '\xef\xbb\xbf',
            ],
            1 => [
                0 => 259,
                1 => 260,
                2 => 261,
                3 => 255,
            ],
            2 => [
                410 => 1,
            ],
            3 => [],
        ],
    ];
    protected $tokens = [
        255 => 'T_BOM',
        256 => 'T_OPEN_TAG_WITH_ECHO',
        257 => 'T_OPEN_TAG',
        258 => 'T_INLINE_HTML',
        259 => 'T_COMMENT',
        260 => 'T_DOC_COMMENT',
        261 => 'T_WHITESPACE',
        262 => 'T_HEREDOC',
        263 => 'T_NOWDOC',
        264 => 'T_INTERPOLATE_STRING',
        265 => 'T_STRING',
        266 => 'T_CAST',
        267 => 'T_HEX_NUMBER',
        268 => 'T_OCT_NUMBER',
        269 => 'T_FLOAT_NUMBER',
        270 => 'T_INT_NUMBER',
        271 => 'T_VARIABLE',
        272 => 'T_NS',
        273 => 'T_OBJECT_OPERATOR',
        274 => 'T_DOUBLE_ARROW',
        275 => 'T_INCREMENT',
        276 => 'T_DECREMENT',
        277 => 'T_COALESCE',
        278 => 'T_POW',
        279 => 'T_AT',
        280 => 'T_PLUS_EQUAL',
        281 => 'T_MINUS_EQUAL',
        282 => 'T_MUL_EQUAL',
        283 => 'T_DIV_EQUAL',
        284 => 'T_CONCAT_EQUAL',
        285 => 'T_MOD_EQUAL',
        286 => 'T_AND_EQUAL',
        287 => 'T_OR_EQUAL',
        288 => 'T_XOR_EQUAL',
        289 => 'T_SL_EQUAL',
        290 => 'T_SR_EQUAL',
        291 => 'T_IDENTICAL',
        292 => 'T_NOT_IDENTICAL',
        293 => 'T_EQUAL',
        294 => 'T_NOT_EQUAL',
        295 => 'T_NOT',
        296 => 'T_ASSIGN',
        297 => 'T_BOOLEAN_AND',
        298 => 'T_BOOLEAN_OR',
        299 => 'T_BIN_AND',
        300 => 'T_BIN_OR',
        301 => 'T_BIN_NOT',
        302 => 'T_ELLIPSIS',
        303 => 'T_MINUS',
        304 => 'T_PLUS',
        305 => 'T_MUL',
        306 => 'T_CONCAT',
        307 => 'T_DIV',
        308 => 'T_MOD',
        309 => 'T_XOR',
        310 => 'T_SPACESHIP',
        311 => 'T_SL',
        312 => 'T_SR',
        313 => 'T_GREATER_OR_EQUAL',
        314 => 'T_GREATER',
        315 => 'T_LESSOR_EQUAL',
        316 => 'T_LESS',
        317 => 'T_SEMICOLON',
        318 => 'T_DOUBLE_COLON',
        319 => 'T_COLON',
        320 => 'T_Q_MARK',
        321 => 'T_COMMA',
        322 => 'T_LOGICAL_OR',
        323 => 'T_LOGICAL_AND',
        324 => 'T_LOGICAL_XOR',
        325 => 'T_PARENTHESIS_OPEN',
        326 => 'T_PARENTHESIS_CLOSE',
        327 => 'T_BRACKET_OPEN',
        328 => 'T_BRACKET_CLOSE',
        329 => 'T_BRACE_OPEN',
        330 => 'T_BRACE_CLOSE',
        331 => 'T_NEW',
        332 => 'T_CLONE',
        333 => 'T_EXIT',
        334 => 'T_IF',
        335 => 'T_ELSEIF',
        336 => 'T_ELSE',
        337 => 'T_ENDIF',
        338 => 'T_ECHO',
        339 => 'T_DO',
        340 => 'T_WHILE',
        341 => 'T_ENDWHILE',
        342 => 'T_FOR',
        343 => 'T_ENDFOR',
        344 => 'T_FOREACH',
        345 => 'T_ENDFOREACH',
        346 => 'T_DECLARE',
        347 => 'T_ENDDECLARE',
        348 => 'T_AS',
        349 => 'T_SWITCH',
        350 => 'T_ENDSWITCH',
        351 => 'T_CASE',
        352 => 'T_DEFAULT',
        353 => 'T_BREAK',
        354 => 'T_CONTINUE',
        355 => 'T_GOTO',
        356 => 'T_FUNCTION',
        357 => 'T_CONST',
        358 => 'T_RETURN',
        359 => 'T_TRY',
        360 => 'T_CATCH',
        361 => 'T_FINALLY',
        362 => 'T_THROW',
        363 => 'T_USE',
        364 => 'T_INSTEADOF',
        365 => 'T_GLOBAL',
        366 => 'T_STATIC',
        367 => 'T_ABSTRACT',
        368 => 'T_FINAL',
        369 => 'T_PRIVATE',
        370 => 'T_PROTECTED',
        371 => 'T_PUBLIC',
        372 => 'T_VAR',
        373 => 'T_UNSET',
        374 => 'T_ISSET',
        375 => 'T_EMPTY',
        376 => 'T_HALT_COMPILER',
        377 => 'T_CLASS',
        378 => 'T_TRAIT',
        379 => 'T_INTERFACE',
        380 => 'T_EXTENDS',
        381 => 'T_IMPLEMENTS',
        382 => 'T_LIST',
        383 => 'T_ARRAY',
        384 => 'T_CALLABLE',
        385 => 'T_LINE',
        386 => 'T_FILE',
        387 => 'T_DIR',
        388 => 'T_CLASS_C',
        389 => 'T_TRAIT_C',
        390 => 'T_METHOD_C',
        391 => 'T_FUNC_C',
        392 => 'T_NS_C',
        393 => 'T_TRUE',
        394 => 'T_FALSE',
        395 => 'T_NULL',
        396 => 'T_NAMESPACE',
        397 => 'T_SELF',
        398 => 'T_INSTANCEOF  ',
        399 => 'T_PARENT',
        400 => 'T_YIELD_FROM',
        401 => 'T_YIELD',
        402 => 'T_DIE',
        403 => 'T_PRINT',
        404 => 'T_EVAL',
        405 => 'T_INCLUDE',
        406 => 'T_INCLUDE_ONCE',
        407 => 'T_REQUIRE',
        408 => 'T_REQUIRE_ONCE',
        409 => 'T_CONST_STRING',
        410 => 'T_CLOSE_TAG',
    ];
};


foreach ($lexer->lex($src = File::fromPathName(__FILE__)) as $token) {
    echo \vsprintf("%-20s (%s)\n", [
        '"' . \str_replace(["\n", "\0"], ['\n', '\0'], $token->getValue()) . '"',
        $lexer->nameOf($token->getType())
    ]);
}
