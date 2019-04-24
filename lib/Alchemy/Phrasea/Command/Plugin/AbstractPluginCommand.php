<?php

/*
 * This file is part of Phraseanet
 *
 * (c) 2005-2016 Alchemy
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Alchemy\Phrasea\Command\Plugin;

use Alchemy\Phrasea\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class AbstractPluginCommand extends Command
{
    protected function validatePlugins(InputInterface $input, OutputInterface $output)
    {
        $manifests = [];

        $output->write("Validating plugins...");
        foreach ($this->container['plugins.explorer'] as $directory) {
            $manifests[] = $this->container['plugins.plugins-validator']->validatePlugin($directory);
        }
        $output->writeln(" <comment>OK</comment>");

        return $manifests;
    }

    protected function doExecute(InputInterface $input, OutputInterface $output)
    {
        if (basename($_SERVER['PHP_SELF']) === 'console') {
            $output->writeln("");
            $output->writeln(sprintf('<error> /!\ </error> <comment>Warning</comment>, this command is deprecated and will be removed as of Phraseanet 3.9, please use <info>bin/setup %s</info> instead <error> /!\ </error>', $this->getName()));
            $output->writeln("");
        }

        return $this->doExecutePluginAction($input, $output);
    }

    abstract protected function doExecutePluginAction(InputInterface $input, OutputInterface $output);

    protected function updateConfigFiles(InputInterface $input, OutputInterface $output)
    {
        $manifests = $this->validatePlugins($input, $output);

        $output->write("Updating config files...");
        $this->container['plugins.autoloader-generator']->write($manifests);
        $output->writeln(" <comment>OK</comment>");
    }

    protected function validateSource($source)
    {
        $valid_scheme = false;
        $valid_extension = false;

        $allowed_scheme = array('https','ssh');
        $allowed_extension = array('zip','git');

        $scheme =  parse_url($source, PHP_URL_SCHEME);
        if (in_array($scheme, $allowed_scheme)){
            $valid_scheme = true;
        }

        $path = parse_url($source, PHP_URL_PATH);
        if (strpos($path, '.') !== false) {
            $path_parts = explode('.', $path);
            $extension = $path_parts[1];
            if (in_array($extension, $allowed_extension)){
                $valid_extension = true;
            }
        }

        if ($valid_scheme && $valid_extension){
            return $extension;
        } else {
            return false;
        }
    }

    public static function delDirTree($dir) {
        $files = array_diff(scandir($dir), array('.','..'));
        foreach ($files as $file) {
            (is_dir("$dir/$file")) ? self::delDirTree("$dir/$file") : unlink("$dir/$file");
        }
        return rmdir($dir);
    }


}
