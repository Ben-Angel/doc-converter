<?php
namespace Userguide\Converter\Plugins;

use dflydev\markdown\MarkdownExtraParser;
use Jig\Utils\FsUtils;
use Userguide\Converter\PluginAbstract;
use Userguide\Converter\PluginInterface;
use Userguide\Helpers\Indexer;

class Ebook extends PluginAbstract implements PluginInterface

{
    protected $baseTmpDir;
    protected $fileListing;

    /**
     * Run the actual conversion from Markdown to target format
     *
     * @param array $fileListing
     *
     * @return mixed
     */
    public function runConversion( array $fileListing )
    {

        $this
            ->init( $fileListing )
            ->prepareMdStructure()
            ->prepareMap()
            ->prepareIndex()
            ->book();
    }

    protected function prepareMdStructure()
    {

        FsUtils::mkDir( $this->baseTmpDir );

        foreach ($this->fileListing as $nodeId => $fileName) {
            $fullTargetPath = $this->baseTmpDir . $this->indexer->getMetaTree()[$nodeId]['flat'] . '.md';
            copy( $fileName, $fullTargetPath );
        }

        copy(
            $this->paths['base'] . $this->paths['trees'] . '/' . Indexer::FILE_MAP_LINKS_FLAT,
            $this->baseTmpDir . Indexer::FILE_MAP_LINKS_FLAT
        );

        return $this;
    }

    private function book()
    {
        $ebook = new \Md2Epub\EBook( $this->baseTmpDir );

        $workingDir = sys_get_temp_dir() . uniqid( 'tao_' );

        if (is_dir( $workingDir )) {
            FsUtils::rmDir( $workingDir );
        }
        FsUtils::mkDir( $workingDir );

        $ebook->makeEpub(
            array(
                'out_file'      => $this->getBaseOutputPath() . '/book.epub',
                'working_dir'   => $workingDir,
                'templates_dir' =>  $this->getResourceDir() ,
                'filters'       => array(
                    'md' => function ( $text ) {
                        static $parser;
                        if ( ! isset( $parser )) {
                            $parser = new MarkdownExtraParser();
                        }

                        return $parser->transform( $text );
                    }
                )
            )
        );

        return $this;
    }

    private function init( array $fileListing )
    {
        $this->baseTmpDir = sys_get_temp_dir() . uniqid( 'tao_' );

        $outputPath = $this->getBaseOutputPath() . '/tmp';
        FsUtils::mkDir( $outputPath );

        $this->fileListing = $fileListing;

        return $this;
    }

    private function prepareMap()
    {

        copy(
            $this->getResourceDir() .  '/book.json',
            $this->baseTmpDir . '/book.json'
        );


        return $this;
    }

    private function prepareIndex()
    {
        //generates index file
        return $this;
    }

}