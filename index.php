<?php
namespace php_require\php_composite;

/*
    Executes the given $source.

    Could do with a refactor.
*/

function dispatch($source) {

    $result = "";
    $buffer = null;

    ob_start();

    switch (gettype($source)) {

        case "object":

            /*
                If we have an object and it's a "Closure" call it.
            */

            if (get_class($source) === "Closure") {
                $result = $source();
            }

            break;

        case "string":

            /*
                If we got a string use call_user_func() on it.
            */

            if (function_exists($source)) {
                $result = call_user_func($source);
            } else {
                $result = $source;
            }

            break;

        case "array":

            /*
                If we got an array then use "php-require".
            */

            global $require; // NOTE: this is the top level $require() var.

            if (isset($source[0]) && gettype($source[0]) === "array") {

                /*
                    If we got an array of array's try again.
                */

                foreach ($source as $module) {
                    $result .= dispatch($module);
                }

            } else {

                /*
                    Here we assume we got a module/action pair.
                */

                if (isset($source["module"]) && isset($source["action"])) {

                    $action = $source["action"];
                    $module = $require($source["module"]);

                    /*
                        Check that we can call a function so the page doesn't bomb with error.
                    */

                    if (isset($module[$source["action"]]) && get_class($module[$source["action"]]) == "Closure") {
                        $result = $module[$source["action"]]();
                    }
                }
            }

            break;
    }

    $buffer = ob_get_contents();
    ob_end_clean();

    /*
        If $buffer has a value then we think there was an error so return the error.
    */

    if ($buffer) {
        return $buffer;
    }

    return $result;
}

/*
    $slots = array(
        "header" => array(
            "module" => "name",
            "action" => "function_name"
        ),
        "main" => function (),
        "footer" => "function_name",
        "sidebar" => array(
            array(
                "module" => "name",
                "action" => "function_name"
            ),
            array(
                "main" => function () {
                    return function ()
                }
            )
        )
    );

    $data = array(
        "title" => "string"
    );

    return array(
        "title" => "string",
        "header" => ".."
        "main" => "..",
        "footer" => ".."
    );
*/

$module->exports = function ($slots, $data=array()) {

    /*
        Here we "dispatch" each $slot we are given adding the result to the $data array.
    */

    foreach ($slots as $slot => $action) {
        $data[$slot] = dispatch($action);
    }

    /*
        In case we got some functions acting as continuations loop over the $data array and check.
    */

    foreach ($data as $key => $val) {
        if (gettype($val) === "object" && get_class($val) === "Closure") {
            $data[$key] = $val();
        }
    }

    /*
        Return the $data array.
    */

    return $data;
};
