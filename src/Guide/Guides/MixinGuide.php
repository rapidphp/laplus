<?php

namespace Rapid\Laplus\Guide\Guides;

use Rapid\Laplus\Editors\GitIgnoreEditor;
use Rapid\Laplus\Guide\Guide;
use Rapid\Laplus\Guide\GuideAuthor;
use Rapid\Laplus\Guide\GuideScope;

class MixinGuide extends Guide
{

    protected $stub;
    protected GuideScope $scope;

    public function __construct(
        protected string $stubPath,
        protected string $stubNamespace = 'Rapid\\_Stub',
        protected bool   $gitIgnore = false,
    )
    {
        if ($this->gitIgnore) {
            GitIgnoreEditor
                ::make(dirname($this->stubPath) . '/.gitignore')
                ->add("./" . pathinfo($this->stubPath, PATHINFO_BASENAME))
                ->save();
        }
    }

    protected function open()
    {
        @mkdir(dirname($this->stubPath), recursive: true);

        if ($this->gitIgnore) {
            $gitIgnoreContents = @file_get_contents(dirname($this->stubPath) . '/.gitignore') ?: "";
            $ignore = "./" . pathinfo($this->stubPath, PATHINFO_BASENAME);
            if (!in_array($ignore, explode("\n", $gitIgnoreContents))) {
                $gitIgnoreContents = "$ignore\n$gitIgnoreContents";
            }
        }

        $this->stub = fopen($this->stubPath, 'c');
        fwrite($this->stub, "<?php\n\nnamespace {$this->stubNamespace};\n\n");

        $this->scope = new GuideScope($this->stubNamespace, []);
    }

    protected function close()
    {
        fclose($this->stub);
    }

    protected function write(GuideAuthor $author)
    {
        $stubName = '_' . md5($author->class);

        $this->modifyFile($author, function ($contents) use ($author, $stubName) {
            return $this->commentClass(
                $contents,
                $author->class,
                'Guide',
                ["@mixin \\{$this->stubNamespace}\\{$stubName}"],
            );
        });

        $comment = "/**\n * " . implode("\n * ", $author->docblock($this->scope)) . "\n **/";

        fwrite($this->stub,
            <<<TEXT
            
            $comment
            class $stubName { }
            
            TEXT,
        );
    }

}