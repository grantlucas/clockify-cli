<?php

return [
    'minimum_severity' => Phan\Issue::SEVERITY_CRITICAL,

    'directory_list' => [
        'src',
        'vendor',
    ],

    'exclude_analysis_directory_list' => [
        'vendor/'
    ],

    // Various settings
    'enable_internal_return_type_plugins' => true,
    'enable_extended_internal_return_type_plugins' => true,
    'warn_about_undocumented_throw_statements' => true,
    'warn_about_undocumented_exceptions_thrown_by_invoked_functions' => true,

    'plugins' => [
        'WhitespacePlugin',
        // checks if a function, closure or method unconditionally returns.
        // can also be written as 'vendor/phan/phan/.phan/plugins/AlwaysReturnPlugin.php'
        'AlwaysReturnPlugin',
        'DollarDollarPlugin',
        'DuplicateArrayKeyPlugin',
        'DuplicateExpressionPlugin',
        'PregRegexCheckerPlugin',
        'PrintfCheckerPlugin',
        'SleepCheckerPlugin',
        // Checks for syntactically unreachable statements in
        // the global scope or function bodies.
        'UnreachableCodePlugin',
        'UseReturnValuePlugin',
        'EmptyStatementListPlugin',
        'LoopVariableReusePlugin',

    ],


];
