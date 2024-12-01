<?php

namespace Composer\Autoload;

class ClassLoader
{
    private $prefixLengthsPsr4 = array();
    private $prefixDirsPsr4 = array();
    private $fallbackDirsPsr4 = array();

    public function getPrefixesPsr4()
    {
        return $this->prefixDirsPsr4;
    }

    public function setPsr4($prefix, $paths)
    {
        if (!$prefix) {
            $this->fallbackDirsPsr4 = (array) $paths;
        } else {
            $length = strlen($prefix);
            if ('\\' !== $prefix[$length - 1]) {
                throw new \InvalidArgumentException("A non-empty PSR-4 prefix must end with a namespace separator.");
            }
            $this->prefixLengthsPsr4[$prefix[0]][$prefix] = $length;
            $this->prefixDirsPsr4[$prefix] = (array) $paths;
        }
    }

    public function register($prepend = false)
    {
        spl_autoload_register(array($this, 'loadClass'), true, $prepend);
    }

    public function loadClass($class)
    {
        if ($file = $this->findFile($class)) {
            includeFile($file);
            return true;
        }
        return false;
    }

    public function findFile($class)
    {
        $prefixLength = $this->prefixLengthsPsr4[$class[0]][$prefix] ?? 0;
        if ($prefixLength) {
            $relative = substr($class, $prefixLength);
            $file = $this->prefixDirsPsr4[$prefix][0] . str_replace('\\', '/', $relative) . '.php';
            if (file_exists($file)) {
                return $file;
            }
        }
        return false;
    }
}

function includeFile($file)
{
    include $file;
}