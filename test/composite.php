<?php

$module = new stdClass();

/*
    Now we "require()" the file to test.
*/

require(__DIR__ . "/../index.php");

$composite = $module->exports;

/*
    Now we test it.
*/

describe("php-composite", function () use ($composite) {

    describe("composite", function () use ($composite) {

        it("should return [true]", function () use ($composite) {
            assert(gettype($composite) === "object" && get_class($composite) === "Closure");
        });

        it("should return [bar]", function () use ($composite) {

            $slots = array(
                "foo" => "bar"
            );

            $result = $composite($slots);

            assert($result["foo"] === "bar");
        });

        it("should return [123]", function () use ($composite) {

            $slots = array(
                "foo" => 123
            );

            $result = $composite($slots);

            assert($result["foo"] === 123);
        });

        it("should return [bar, qux]", function () use ($composite) {

            $slots = array(
                "foo" => "bar",
                "baz" => "qux"
            );

            $result = $composite($slots);

            assert($result["foo"] === "bar");
            assert($result["baz"] === "qux");
        });

        it("should return [bar] from an anonymous function", function () use ($composite) {

            $slots = array(
                "foo" => function () {
                    return "bar";
                }
            );

            $result = $composite($slots);

            assert($result["foo"] === "bar");
        });

        it("should return [bar] from a callback anonymous function", function () use ($composite) {

            $slots = array(
                "foo" => function () {
                    return function () {
                        return "bar";
                    };
                }
            );

            $result = $composite($slots);

            assert($result["foo"] === "bar");
        });

        it("should return [bar] from a function", function () use ($composite) {

            function foo() {
                return "bar";
            }

            $slots = array(
                "foo" => "foo"
            );

            $result = $composite($slots);

            assert($result["foo"] === "bar");
        });

        it("should return [bar] from module1", function () use ($composite) {

            $slots = array(
                "foo" => array(
                    "module" => "./test/fixtures/module1/index.php",
                    "action" => "foo"
                )
            );

            $result = $composite($slots);

            assert($result["foo"] === "bar");
        });

        it("should return [bar] from module4 with params", function () use ($composite) {

            $slots = array(
                "foo" => array(
                    "module" => "./test/fixtures/module4/index.php",
                    "action" => "foo",
                    "params" => array(
                        "foo" => "bar"
                    )
                )
            );

            $result = $composite($slots);

            assert($result["foo"] === "bar");
        });

        it("should return [baz] from module2", function () use ($composite) {

            $slots = array(
                "foo" => array(
                    "module" => "./test/fixtures/module1/index.php",
                    "action" => "foo"
                ),
                "bar" => array(
                    "module" => "./test/fixtures/module2/index.php",
                    "action" => "bar"
                )
            );

            $result = $composite($slots);

            assert($result["bar"] === "baz");
        });

        it("should return [barbaz] from module1 and module2", function () use ($composite) {

            $slots = array(
                "qux" => array(
                    array(
                        "module" => "./test/fixtures/module1/index.php",
                        "action" => "foo"
                    ),
                    array(
                        "module" => "./test/fixtures/module2/index.php",
                        "action" => "bar"
                    )
                )
            );

            $result = $composite($slots);

            assert($result["qux"] === "barbaz");
        });

        it("should return [barbaz] from module1 and module2", function () use ($composite) {

            $slots = array(
                "qux" => array(
                    array(
                        "module" => "./test/fixtures/module1/index.php",
                        "action" => "foo"
                    ),
                    array(
                        "module" => "./test/fixtures/module2/index.php",
                        "action" => "bar"
                    )
                )
            );

            $result = $composite($slots, null, true);

            assert(strpos($result["qux"], "class=\"module-time\"") > 0);
        });

        it("should return [error] from buffer", function () use ($composite) {

            $slots = array(
                "foo" => array(
                    "module" => "./test/fixtures/module3/index.php",
                    "action" => "error"
                )
            );

            $result = $composite($slots);

            assert($result["foo"] === "error");
        });
    });
});
