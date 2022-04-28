<?php

namespace Lantern\Features\Constraints;

use Lantern\Lantern;
use Symfony\Component\Process\ExecutableFinder;

class ExecutableIsInstalled implements Constraint
{
    protected $executableName;

    public function __construct($executableName)
    {
        $this->executableName = $executableName;
    }

    public function isMet(): bool
    {
        if (! $this->findExecutable($this->executableName)) {
            return false;
        }

        return true;
    }

    public function description(): string
    {
        return sprintf('Executable "%s" must be installed', $this->executableName);
    }

    /**
     * @param string $name of program without any path info
     * @return string
     */
    protected function findExecutable($name)
    {
        $finder = new ExecutableFinder();

        $extraDirs = Lantern::pathDirs();

        return $finder->find($name, null, $extraDirs);
    }
}
