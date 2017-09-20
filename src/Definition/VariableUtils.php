<?php

namespace TheCodingMachine\Yaco\Definition;

class VariableUtils
{
    /**
     * Returns the next available variable name to use.
     *
     * @param string $variable      The variable name we wish to use
     * @param array  $usedVariables The list of variable names already used.
     *
     * @return string
     */
    public static function getNextAvailableVariableName(string $variable, array $usedVariables): string
    {
        $variable = self::toVariableName($variable);
        while (true) {
            // check that the name is not reserved
            if (!in_array($variable, $usedVariables, true)) {
                break;
            }

            $numbers = '';
            while (true) {
                $lastCharacter = substr($variable, strlen($variable) - 1);
                if ($lastCharacter >= '0' && $lastCharacter <= '9') {
                    $numbers = $lastCharacter.$numbers;
                    $variable = substr($variable, 0, strlen($variable) - 1);
                } else {
                    break;
                }
            }

            if ($numbers === '') {
                $numbers = 0;
            } else {
                $numbers = (int) $numbers;
            }
            ++$numbers;

            $variable = $variable.$numbers;
        }

        return $variable;
    }

    /**
     * Transform $name into a valid variable name by removing not authorized characters or adding new ones.
     *
     * - foo => $foo
     * - $foo => $foo
     * - fo$o => $foo
     *
     * @param string $name
     *
     * @return string
     */
    private static function toVariableName(string $name): string
    {
        $variableName = preg_replace('/[^A-Za-z0-9]/', '', $name);
        if ($variableName{0} >= '0' && $variableName{0} <= '9') {
            $variableName = 'a'.$variableName;
        }

        return '$'.$variableName;
    }
}
