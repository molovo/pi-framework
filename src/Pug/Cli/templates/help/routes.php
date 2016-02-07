<?php

return <<<EOF
\e[0;33mUsage:\e[0;m
    ./pug routes [<scope>] [options]

\e[0;33mScopes:\e[0;m
    \e[0;32mall\e[0;m         Show a table containing all routes \e[0;35m(Default)\e[0;m
    \e[0;32mtest\e[0;m \e[0;35m<uri>\e[0;m  Test a URI to find the matching route
EOF;
