<?php
namespace TheCodingMachine\Yaco;
use Interop\Container\Compiler\DefinitionInterface;
use Interop\Container\Compiler\InlineEntryInterface;

/**
 * A class that generates a PHP class (a container) from definitions.
 */
class Compiler
{

    /**
     * @var DefinitionInterface[]
     */
    private $definitions = [];

    /**
     * @param DefinitionInterface $definition
     */
    public function addDefinition(DefinitionInterface $definition) {
        $this->definitions[$definition->getIdentifier()] = $definition;
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
            $inlineEntry = $definition->toPhpCode('$container', ['$container']);

            if ($inlineEntry->isLazilyEvaluated()) {
                $closuresCode .= "            ".var_export($identifier, true)." => ".$this->getClosureCode($inlineEntry).",\n";
            } else {
                $parametersCode .= "            ".var_export($identifier, true)." => ".$this->getParametersCode($inlineEntry).",\n";
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

    private function getParametersCode(InlineEntryInterface $inlineEntry) {
        if (!empty($inlineEntry->getStatements())) {
            throw new CompilerException('An entry that contains parameters (not lazily loaded) cannot return statements.');
        }
        return $inlineEntry->getExpression();
    }

    private function getClosureCode(InlineEntryInterface $inlineEntry) {
        $code = $inlineEntry->getStatements();
        $code .= "return ".$inlineEntry->getExpression().";\n";
        return sprintf("function(\$container) {\n%s}", $code);
    }
}
