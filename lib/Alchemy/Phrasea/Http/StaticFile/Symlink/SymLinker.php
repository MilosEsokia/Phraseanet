<?php

/*
 * This file is part of Phraseanet
 *
 * (c) 2005-2014 Alchemy
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Alchemy\Phrasea\Http\StaticFile\Symlink;

use Silex\Application;
use Symfony\Component\Filesystem\Filesystem;
use Guzzle\Http\Url;

/**
 * Create & retrieve symlinks from public directory
 */
class SymLinker
{
    const ALIAS = 'thumb';

    protected $encoder;
    protected $fs;
    protected $publicDir;
    protected $registry;
    protected $rootPath;

    public static function create(Application $app)
    {
        return new SymLinker(
            $app['phraseanet.thumb-symlinker-encoder'],
            $app['filesystem'],
            $app['phraseanet.registry'],
            $app['root.path']
        );
    }

    public function __construct(SymLinkerEncoder $encoder, Filesystem $fs, \registryInterface $registry, $rootPath)
    {
        $this->encoder = $encoder;
        $this->fs = $fs;
        $this->registry = $registry;
        $this->rootPath = $rootPath;
        $this->publicDir = sprintf('%s/public/thumbnails', rtrim($this->rootPath, '/'));
    }

    public function getPublicDir()
    {
        return $this->publicDir;
    }

    public function getDefaultAlias()
    {
        return sprintf('/%s', self::ALIAS);
    }

    public function symlink($pathFile)
    {
        $this->fs->symlink($pathFile, $this->getSymlinkPath($pathFile)) ;
    }

    public function getSymlink($pathFile)
    {
        return $this->encoder->encode($pathFile);
    }

    public function getSymlinkBasePath($pathFile)
    {
        $symlinkName = $this->getSymlink($pathFile);

        return sprintf('%s/%s/%s',
            substr($symlinkName, 0, 2),
            substr($symlinkName, 2, 2),
            substr($symlinkName, 4)
        );
    }

    public function getSymlinkPath($pathFile)
    {
        return sprintf(
            '%s/%s',
            $this->publicDir,
            $this->getSymlinkBasePath($pathFile)
        );
    }
}
