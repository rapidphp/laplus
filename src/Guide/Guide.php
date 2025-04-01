<?php

namespace Rapid\Laplus\Guide;

use Closure;
use Illuminate\Support\Facades\File;
use InvalidArgumentException;
use ReflectionClass;

abstract class Guide
{

    public function run(GuideAuthor|array $authors)
    {
        $this->open();

        foreach (is_array($authors) ? $authors : [$authors] as $author) {
            $this->write($author);
        }

        $this->close();
    }

    protected function open()
    {
    }

    protected abstract function write(GuideAuthor $author);

    protected function close()
    {
    }

    protected function modifyFile(GuideAuthor $author, Closure $callback)
    {
        $fileName = $this->guessFileName($author->class);

        $originalContents = File::get($fileName, true);

        $newContents = $callback($originalContents);

        if ($originalContents != $newContents) {
            File::put($fileName, $newContents, true);
        }
    }


    /**
     * Guess the file name for a class
     *
     * @param string $class
     * @return string
     */
    protected function guessFileName(string $class): string
    {
        $fileName = (new ReflectionClass($class))->getFileName();

        if ($fileName && file_exists($fileName)) {
            return $fileName;
        }

        throw new Exceptions\TargetFileNotResolved("No file found for class [$class]");
    }

    /**
     * Comment a class
     *
     * @param string $contents
     * @param string $class
     * @param string $tag
     * @param array $comment
     * @param bool $insertAtLast
     * @param bool $nullOnError
     * @return string|null
     */
    protected function commentClass(
        string $contents,
        string $class,
        string $tag,
        array  $comment,
        bool   $insertAtLast = true,
        bool   $nullOnError = false,
    ): ?string
    {
        $enter = $this->detectEnter($contents);
        $classRegex = preg_quote(class_basename($class), '/');

        if (!preg_match('/^([\s\S]*\n)(\s*)class\s+' . $classRegex . '[\s\r\n]/i', $contents, $matches)) {
            if ($nullOnError) {
                return null;
            }

            throw new Exceptions\FailedToWriteComment(
                "Failed to write comment for class [$class], because it's not exists",
            );
        }

        $left = substr($contents, 0, strlen($matches[1]));
        $center = substr($contents, strlen($matches[1]), strlen($matches[0]) - strlen($matches[1]));
        $right = substr($contents, strlen($matches[0]));

        $spaces = $matches[2];

        while (preg_match('/#\[\s*[a-zA-Z0-9\\\\_]+(|\s*\([\s\S]*)\s*][\s\r\n]*$/', $left, $matches)) {
            $left = substr($left, 0, -strlen($matches[0]));
            $center = $matches[0] . $center;
        }

        $comment = array_map(fn($c) => "$spaces * $c", $comment);
        if (preg_match('/\/\*\*\s*(\n[\s\S]*?)(\s*\*\/)[\s\r\n]*$/', $left, $commentMatches, PREG_OFFSET_CAPTURE)) {
            $tagRegex = preg_quote($tag, '/');
            if (preg_match(
                '/\* @' . $tagRegex . '\s*\n([\s\S]*?)\n\s*\* @End' . $tagRegex . '\s*(\n|$)/', $commentMatches[1][0],
                $tagMatches, PREG_OFFSET_CAPTURE,
            )) {
                $left = substr_replace(
                    $left,
                    implode($enter, $comment),
                    $commentMatches[1][1] + $tagMatches[1][1],
                    strlen($tagMatches[1][0]),
                );
            } else {
                $left = substr_replace(
                    $left,
                    "$enter$spaces * $enter$spaces * @$tag$enter" . implode(
                        $enter, $comment,
                    ) . "$enter$spaces * @End$tag",
                    $commentMatches[2][1],
                    0,
                );
            }
        } else {
            $left .= "$spaces/**$enter$spaces * @$tag$enter" . implode(
                    $enter, $comment,
                ) . "$enter$spaces * @End$tag$enter$spaces */$enter";
        }

        return $left . $center . $right;
    }

    /**
     * Detect enter value
     *
     * @param string $contents
     * @return string
     */
    protected function detectEnter(string $contents): string
    {
        return str_contains($contents, "\r\n") ? "\r\n" : "\n";
    }

    /**
     * Make new scope by contents
     *
     * @param string $contents
     * @return GuideScope
     */
    protected function makeScope(string $contents): GuideScope
    {
        [$namespace, $className, $uses] = $this->extractContentsData($contents);

        return new GuideScope($namespace, $uses);
    }

    /**
     * Extract contents data
     *
     * @param string $contents
     * @return array
     */
    protected function extractContentsData(string $contents): array
    {
        if (!preg_match('/[\n\s]class\s+([a-zA-Z0-9_]+)/', $contents, $matches, PREG_OFFSET_CAPTURE)) {
            throw new InvalidArgumentException();
        }

        $contents = substr($contents, 0, $matches[0][1]);
        $className = $matches[1][0];

        if (preg_match('/[\n\s]namespace\s+([a-zA-Z0-9_\\\\]+)/', $contents, $matches)) {
            $namespace = $matches[1];
        } else {
            $namespace = null;
        }

        $uses = [];
        if (preg_match_all('/[\n\s]use\s+([a-zA-Z0-9_\\\\]+)(\s+as\s+([a-zA-Z0-9_\\\\]+))?\s*;/', $contents, $matches)) {
            foreach ($matches[1] as $i => $use) {
                $uses[$use] = @$matches[3][$i] ?: class_basename($use);
            }
        }

        return [$namespace, $className, $uses];
    }

}