<?php
use Fedeisas\LaravelMailCssInliner\ExternalCssPlugin;

class ExternalCssPluginTest extends PHPUnit_Framework_TestCase
{
    protected $stubs;

    public function setUp()
    {
        $this->stubs['plain-text'] = file_get_contents(__DIR__.'/stubs/plain-text.stub');
        $this->stubs['external-style'] = file_get_contents(__DIR__.'/stubs/externalStyle.css');
        $this->stubs['original-html'] = file_get_contents(__DIR__.'/stubs/original-html-external.stub');
        $this->stubs['converted-html'] = file_get_contents(__DIR__.'/stubs/converted-html-external.stub');
    }

    /** @test **/
    public function getHtmlBody()
    {
        $external = new ExternalCssPlugin();
        $orginalHtml = str_replace('%styleUrl%', __DIR__.'/stubs/externalStyle.css', $this->stubs['original-html']);
        $newHtml = $external->includeExternalStylesheets($orginalHtml);

        $this->assertEquals($this->stubs['converted-html'], $newHtml);
    }

    /** @test **/
    public function checkCss()
    {
        $external = new ExternalCssPlugin();
        $getStyle = $external->getCssContent('<link rel="stylesheet" href="'.__DIR__.'/stubs/externalStyle.css">');
        $this->assertEquals($this->stubs['external-style'], $getStyle);
    }
}
