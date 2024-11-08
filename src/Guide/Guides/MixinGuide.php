<?php

namespace Rapid\Laplus\Guide\Guides;

use Rapid\Laplus\Guide\Guide;
use Rapid\Laplus\Guide\GuideAuthor;
use Rapid\Laplus\Guide\GuideScope;

class MixinGuide extends Guide
{

    public function __construct(
        protected string $stubPath,
        protected string $stubNamespace = 'Rapid\\_Stub',
    )
    {
    }

    protected $stub;

    protected GuideScope $scope;

    protected function open()
    {
        $this->stub = fopen($this->stubPath, 'c');
        fwrite($this->stub, "<?php\n\nnamespace {$this->stubNamespace}\n\n");

        $this->scope = new GuideScope($this->stubNamespace, []);
    }

    protected function close()
    {
        fclose($this->stub);
    }

    protected function write(GuideAuthor $author)
    {
        $stubName = md5($author->class);

        $this->modifyFile($author, function ($contents) use ($author, $stubName)
        {
            return $this->commentClass(
                $contents,
                $author->class,
                'Guide',
                ["@mixin \\{$this->stubNamespace}\\{$stubName}"]
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