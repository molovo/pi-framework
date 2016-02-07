<?php

return <<<EOF
\e[0;33mUsage:\e[0;m
    ./pug compile [<scope>] [options]

\e[0;33mScopes:\e[0;m
    \e[0;32mall\e[0;m         Compile all pages and assets \e[0;35m(Default)\e[0;m
    \e[0;32massets\e[0;m      Compile all assets
    \e[0;32mpages\e[0;m       Compile pages
    \e[0;32mjs\e[0;m          Compile JavaScript
    \e[0;32mcss\e[0;m         Compile CSS
    \e[0;32msass\e[0;m        Compile SASS
    \e[0;32mless\e[0;m        Compile Less
    \e[0;32mcoffee\e[0;m      Compile CoffeeScript
EOF;
