<?php

return <<<EOF
\e[0;33mUsage:\e[0;m
    ./pug <command> [arguments]

\e[0;33mCommands:\e[0;m
    \e[0;32mhelp\e[0;m        Displays this help text
    \e[0;32mcompile\e[0;m     Compile static resources for the site
    \e[0;32mroutes\e[0;m      Display a table containing all the app's routes

Run \e[0;34m./pug <command> help\e[0;m to see help for each individual command
EOF;
