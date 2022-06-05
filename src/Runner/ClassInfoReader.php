<?php

namespace Lucinda\UnitTest\Runner;

/**
 * Reads PHP file and detects class/interface info relevant to unit testing: inheritance architecture, public methods
 */
class ClassInfoReader
{
    private ClassInfo $classInfo;

    /**
     * @param string $filePath
     */
    public function __construct(string $filePath)
    {
        $contents = $this->getContents($filePath);
        $this->setClassInfo($filePath, $contents);
    }

    /**
     * Reads source file and detects class/interface info
     *
     * @param  string $filePath
     * @param  string $content
     * @return void
     */
    private function setClassInfo(string $filePath, string $content): void
    {
        $classInfo = new ClassInfo();
        $classInfo->filePath = $filePath;
        $classInfo->namespace = $this->getNamespace($content);
        $classInfo->methods = $this->getMethods($content);

        $correspondences = $this->getUses($content);

        $matches1 = [];
        preg_match(
            "/(\n[\s|\t]*(final)?\s*(abstract)?\s*(class|interface|enum)\s*([a-zA-Z0-9_]+))\s*(extends\s+([a-zA-Z0-9_\\\]+))?\s*(implements\s+([a-zA-Z0-9_\\\,\s]+))?/",
            $content,
            $matches1
        );
        $classInfo->isFinal = (bool)$matches1[2];
        $classInfo->isAbstract = (bool)$matches1[3];
        $classInfo->isInterface = $matches1[4]=="interface";
        $classInfo->isEnum = $matches1[4]=="enum";
        $classInfo->className = $matches1[5];
        $classInfo->extends = (!empty($matches1[7]) ? $this->getFullClassName($matches1[7], $correspondences, $classInfo->namespace) : "");
        if (!empty($matches1[9])) {
            $matches2 = [];
            preg_match_all("/([_a-zA-Z0-9_\\\]+)/", $matches1[9], $matches2);
            foreach ($matches2[1] as $className) {
                $classInfo->implements[] = $this->getFullClassName($className, $correspondences, $classInfo->namespace);
            }
        }

        $this->classInfo = $classInfo;
    }

    /**
     * Gets class info detected
     *
     * @return ClassInfo
     */
    public function getClassInfo(): ClassInfo
    {
        return $this->classInfo;
    }

    /**
     * Gets contents of PHP file, stripping comments
     *
     * @param  string $path
     * @return string
     * @see    https://stackoverflow.com/questions/503871/best-way-to-automatically-remove-comments-from-php-code
     */
    private function getContents(string $path): string
    {
        $str = file_get_contents($path);
        $commentTokens = [
            \T_COMMENT,
            \T_DOC_COMMENT,
        ];
        $tokens = token_get_all($str);
        $lines = explode(PHP_EOL, $str);
        $output = '';
        foreach ($tokens as $token) {
            if (is_array($token)) {
                if (in_array($token[0], $commentTokens)) {
                    $comment = $token[1];
                    $lineNb = $token[2];
                    $firstLine = $lines[$lineNb - 1];
                    $parts = explode(PHP_EOL, $comment);
                    $nbLineComments = count($parts);
                    if ($nbLineComments < 1) {
                        $nbLineComments = 1;
                    }
                    $firstCommentLine = array_shift($parts);

                    $isStandAlone = (trim($firstLine) === trim($firstCommentLine));

                    if (false === $isStandAlone) {
                        if (2 === $nbLineComments) {
                            $output .= PHP_EOL;
                        }
                        continue; // just remove inline comments
                    }

                    // stand-alone case
                    $output .= str_repeat(PHP_EOL, $nbLineComments - 1);
                    continue;
                }
                $token = $token[1];
            }
            $output .= $token;
        }
        return $output;
    }

    /**
     * Gets full class name
     *
     * @param  string               $name
     * @param  array<string,string> $correspondences
     * @param  string               $namespace
     * @return string
     */
    private function getFullClassName(string $name, array $correspondences, string $namespace): string
    {
        return ($correspondences[$name] ?? ($name[0] == "\\" ? $name : ($namespace ? $namespace."\\".$name : $name)));
    }

    /**
     * Detects namespace from source class
     *
     * @param  string $content
     * @return string
     */
    private function getNamespace(string $content): string
    {
        $matches = [];
        preg_match(
            "/(\n[\s|\t]*namespace\s*([^;]+))/",
            $content,
            $matches
        );
        return ($matches[2] ?? "");
    }

    /**
     * Detects public methods from source class
     *
     * @param  string $content
     * @return array<string,string>
     */
    private function getMethods(string $content): array
    {
        $output = [];
        $matches = [];
        preg_match_all(
            "/(\n[\s|\t]*(final)?\s*(public)?\s*(static)?\s*function\s+([_a-zA-Z0-9]+))/",
            $content,
            $matches
        );
        $methods = ($matches[5] ?? []);
        foreach ($methods as $method) {
            if (!in_array($method, ["__construct", "__destruct"])) {
                $output[$method] = $method;
            }
        }
        return $output;
    }

    /**
     * Reads use statements from source class
     *
     * @param  string $content
     * @return array<string,string>
     */
    private function getUses(string $content): array
    {
        $matches = [];
        preg_match_all(
            "/(\n[\s|\t]*use\s+([a-zA-Z0-9_\\\]+)\s+as\s+([a-zA-Z0-9_]+)\s*;)/",
            $content,
            $matches
        );
        $correspondences = [];
        foreach ($matches[1] as $i=>$value) {
            $correspondences[$matches[2][$i]] = $value;
        }
        return $correspondences;
    }
}
