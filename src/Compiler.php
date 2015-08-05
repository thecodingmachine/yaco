<?php
namespace TheCodingMachine\Yaco;
use Interop\Container\Compiler\DefinitionInterface;

/**
 * A class that generates a PHP class (a container) from definitions.
 */
class Compiler
{

    private $definitions = [];

    public function addDefinition($identifier, DefinitionInterface $definition) {
        $this->definitions[$identifier] = $definition;
    }

    /**
     * @param string $className
     * @return string
     */
    public function compile($className) {

        $classCode = <<<EOF
<?php
%s

use Mouf\Picotainer\Picotainer;

class %s extends Picotainer
{
    public function __construct(ContainerInterface \$delegateLookupContainer = null) {
        parent::__construct([
%s        ], \$delegateLookupContainer);
        \$this->objects = [
%s        ];
    }
}

EOF;

        list($shortClassName, $namespaceLine) = $this->splitFQCN($className);

        $closuresCode = "";
        $parametersCode = "";

        foreach ($this->definitions as $identifier => $definition) {
            $phpCode = $definition->toPhpCode();
            if (self::isClosure($phpCode)) {
                $closuresCode .= "            ".var_export($identifier, true)." => ".$phpCode.",\n";
            } else {
                $parametersCode .= "            ".var_export($identifier, true)." => ".$phpCode.",\n";
            }
        }

        return sprintf($classCode, $namespaceLine, $shortClassName, $closuresCode, $parametersCode);
    }

    private function splitFQCN($className) {
        $pos = strrpos($className, '\\');
        if ($pos !== false) {
            $shortClassName = substr($className, $pos+1);
            $namespaceLine = "namespace ".substr($className, 0, $pos).";";
        } else {
            $shortClassName = $className;
            $namespaceLine = "";
        }
        return [
            $shortClassName,
            $namespaceLine
        ];
    }

    /**
     * Analyzes the PHP code and decides if this should be a closure or not.
     *
     * @param string $phpCode
     * @return bool
     */
    private static function isClosure($phpCode) {
        $phpCode = ltrim($phpCode);
        if (strpos($phpCode, 'function') === 0) {
            $phpCode = ltrim(substr($phpCode, 8));
            if (strpos($phpCode, '(') === 0) {
                return true;
            }
        }
        return false;
    }
}
