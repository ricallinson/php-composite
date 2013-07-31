<?php
namespace php_require\php_composite;

/*
    Executes the given $source.
*/

function dispatch($source) {

    $type = gettype($source);

    switch ($type) {

        /*
            If we have an object and it's a "Closure" call it.
        */

        case "object":

            if (get_class($source) === "Closure") {
                return $source();
            }
            break;

        /*
            If we got a string use call_user_func() on it.
        */

        case "string":

            return call_user_func($source);

        /*
            If we got an array then use "php-require".
        */

        case "array":

            global $require; // NOTE: this is the top level $require

            if (isset($source[0]) && gettype($source[0]) === "array") {

                /*
                    If we got an array of array's try again.
                */

                $return = "";
                foreach ($source as $module) {
                    $return .= dispatch($module);
                }
                return $return;

            } else {

                /*
                    Here we assume we got a module/action pair.
                */

                $action = $source["action"];
                $module = $require($source["module"]);
                return $module[$source["action"]]();
            }
    }
}

/*
    $slots = array(
        "header" => array(
            "module" => "name",
            "func" => "function_name"
        ),
        "main" => function (),
        "footer" => "function_name"
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
