<?php
/**
 * @brief use this script to unbuild XML files to their original .php/phtml/js files. Supply the application key as solo argument.
 * @copyright jannes-io <https://github.com/jannes-io/>
 * @example php unbuildApplication.php perscom
 */

$application = $argv[1];
if (empty($application)) {
    throw new InvalidArgumentException('Argument 1 "application key" is missing. I.E. core, forums,...');
}

$dir = __DIR__ . '/applications/' . $application;

class ApplicationUnbuilder
{
    public function unbuild(string $dir): void
    {
        $this->unbuildLang($dir);
        $this->unbuildTheme($dir);
    }

    private function unbuildLang(string $dir): void
    {
        $langXML = $this->readXML($dir . '/data/lang.xml');

        $langFile = "<?php\n\$lang = [\n";
        foreach ($langXML->app->word as $word) {
            $key = (string)$word->attributes()->key;
            $val = addslashes((string)$word);
            $langFile .= "'{$key}' => '$val',\n";
        }
        $langFile .= "];";
        file_put_contents($dir . '/dev/lang.php', $langFile);
    }

    private function unbuildTheme(string $dir)
    {
        $themeXML = $this->readXML($dir . '/data/theme.xml');

        foreach ($themeXML->template as $template) {
            $location = (string)$template->attributes()->template_location;
            $group = (string)$template->attributes()->template_group;
            $name = (string)$template->attributes()->template_name;
            $data = (string)$template->attributes()->template_data;
            $content = (string)$template;

            $phtmlContent = "<ips:template parameters=\"{$data}\" />\n" . $content;
            $templateDir = "$dir/dev/html/$location/$group";
            if (!is_dir($templateDir)) {
                mkdir($templateDir, 0777, true);
            }
            file_put_contents("$dir/dev/html/$location/$group/$name.phtml", $phtmlContent);
        }
    }

    private function readXML(string $filename): SimpleXMLElement
    {
        $langXML = file_get_contents($filename);
        return simplexml_load_string($langXML, 'SimpleXMLElement', LIBXML_NOCDATA);
    }
}

(new ApplicationUnbuilder())->unbuild($dir);
