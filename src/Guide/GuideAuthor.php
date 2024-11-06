<?php

namespace Rapid\Laplus\Guide;

use PHPUnit\Framework\Assert;

/**
 * @internal
 */
abstract class GuideAuthor
{

    public function __construct(
        public string $class,
    )
    {
    }

    /**
     * Guess the file name for a class
     *
     * @param string|null $class
     * @return string
     */
    protected function guessFileName(?string $class = null) : string
    {
        $class ??= $this->class;

        $fileName = (new \ReflectionClass($class))->getFileName();

        if ($fileName && file_exists($fileName))
        {
            return $fileName;
        }

        throw new Exceptions\TargetFileNotResolved("No file found for class [$class]");
    }

    /**
     * Detect enter value
     *
     * @param string $contents
     * @return string
     */
    protected function detectEnter(string $contents) : string
    {
        return str_contains($contents, "\r\n") ? "\r\n" : "\n";
    }

    /**
     * Comment a class
     *
     * @param string $contents
     * @param string $class
     * @param string $tag
     * @param array  $comment
     * @param bool   $insertAtLast
     * @param bool   $nullOnError
     * @return string|null
     */
    protected function commentClass(
        string $contents,
        string $class,
        string $tag,
        array  $comment,
        bool   $insertAtLast = true,
        bool   $nullOnError = false,
    ) : ?string
    {
        $enter = $this->detectEnter($contents);
        $classRegex = preg_quote(class_basename($class), '/');

        if (!preg_match('/^([\s\S]*\n)(\s*)class\s+' . $classRegex . '[\s\r\n]/i', $contents, $matches))
        {
            if ($nullOnError)
            {
                return null;
            }

            throw new Exceptions\FailedToWriteComment(
                "Failed to write comment for class [$class], because it's not exists"
            );
        }

        $left = substr($contents, 0, strlen($matches[1]));
        $center = substr($contents, strlen($matches[1]), strlen($matches[0]) - strlen($matches[1]));
        $right = substr($contents, strlen($matches[0]));

        $spaces = $matches[2];

        while (preg_match('/#\[\s*[a-zA-Z0-9\\\\_]+(|\s*\([\s\S]*)\s*][\s\r\n]*$/', $left, $matches))
        {
            $left = substr($left, 0, -strlen($matches[0]));
            $center = $matches[0] . $center;
        }

        $comment = array_map(fn ($c) => "$spaces * $c", $comment);
        if (preg_match('/\/\*\*\s*(\n[\s\S]*?)(\s*\*\/)[\s\r\n]*$/', $left, $commentMatches, PREG_OFFSET_CAPTURE))
        {
            $tagRegex = preg_quote($tag, '/');
            if (preg_match(
                '/\* @' . $tagRegex . '\s*\n([\s\S]*?)\n\s*\* @End' . $tagRegex . '\s*(\n|$)/', $commentMatches[1][0],
                $tagMatches, PREG_OFFSET_CAPTURE
            ))
            {
                $left = substr_replace(
                    $left,
                    implode($enter, $comment),
                    $commentMatches[1][1] + $tagMatches[1][1],
                    strlen($tagMatches[1][0])
                );
            }
            else
            {
                $left = substr_replace(
                    $left,
                    "$enter$spaces * $enter$spaces * @$tag$enter" . implode(
                        $enter, $comment
                    ) . "$enter$spaces * @End$tag",
                    $commentMatches[2][1],
                    0,
                );
            }
        }
        else
        {
            $left .= "$spaces/**$enter$spaces * @$tag$enter" . implode(
                    $enter, $comment
                ) . "$enter$spaces * @End$tag$enter$spaces */$enter";
        }

        return $left . $center . $right;
    }

    /**
     * Write and edit the file
     *
     * @return void
     */
    public function write() : void
    {
        $fileName = $this->guessFileName($this->class);

        $contents = file_get_contents($fileName);
        $contents = $this->guide($contents);
        file_put_contents($fileName, $contents);
    }

    /**
     * Write in the test mode in the string contents
     *
     * @param ?string $contents
     * @return string
     */
    public function testWrite(?string $contents = null) : string
    {
        $contents ??= file_get_contents($this->guessFileName($this->class));

        return $this->guide($contents);
    }

    /**
     * Write guide to string contents
     *
     * @return mixed
     */
    protected abstract function guide(string $contents);


    /**
     * Assert insert comment
     *
     * @param string $fullComment
     * @return void
     */
    public function assertInsertComment(string $fullComment)
    {
        $in = "<?php\n" .
            "\n" .
            "class " . class_basename($this->class) . " extends Model {}\n";

        $out = "<?php\n" .
            "\n" .
            $fullComment . "\n" .
            "class " . class_basename($this->class) . " extends Model {}\n";

        Assert::assertSame($out, $this->testWrite($in));
    }

    protected function objectToDoc(mixed $object) : string
    {
        return match (gettype($object))
        {
            'string'            => "\"" . addslashes($object) . "\"",
            'boolean'           => $object ? 'true' : 'false',
            // 'NULL'              => 'null',
            'integer', 'double' => "$object",
            'array'             => $this->arrayToDoc($object),
            default             => 'null',
        };
    }

    protected function arrayToDoc(array $array) : string
    {
        $doc = [];
        $i = 0;
        foreach ($array as $key => $value)
        {
            if (is_int($key) && $key == $i)
            {
                $doc[] = $this->objectToDoc($value);
                $i++;
            }
            else
            {
                $doc[] = $this->objectToDoc($key) . ' => ' . $this->objectToDoc($value);
            }
        }

        return '[' . implode(', ', $doc) . ']';
    }

}