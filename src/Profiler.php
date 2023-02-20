<?php

namespace G4\Runner;

use G4\Profiler\Ticker\TickerAbstract;

class Profiler
{
    const LOG_OFF = 0;
    const LOG_ERRORS_ONLY = 1;
    const LOG_ALWAYS = 2;

    /**
     * @var array
     */
    private $formatted;

    /**
     * @var array|TickerAbstract[]
     */
    private $profilers;

    /**
     * @var int
     */
    private $logLevel;

    public function __construct()
    {
        $this->profilers = [];
        $this->logLevel = self::LOG_ALWAYS;
    }

    /**
     * @param TickerAbstract $profiler
     * @return Profiler
     */
    public function addProfiler(TickerAbstract $profiler)
    {
        $this->profilers[] = $profiler;
        return $this;
    }

    public function clearProfilers()
    {
        foreach ($this->profilers as $aProfiler) {
            $aProfiler->clear();
        }
    }

    public function setLogLevel($logLevel)
    {
        $this->logLevel = (int) $logLevel;
        return $this;
    }

    /**
     * @return array
     */
    public function getProfilerOutput($httpCode)
    {
        return $this->hasProfilers() && $this->shouldLogProfiler($httpCode)
            ? $this->getFormatted()
            : [];
    }

    public function getProfilerSummary()
    {
        return (new ProfilerSummary($this->profilers))->getSummary();
    }

    private function shouldLogProfiler($httpCode)
    {
        if ($this->logLevel === self::LOG_OFF) {
            return false;
        }
        if ($this->logLevel === self::LOG_ALWAYS) {
            return true;
        }
        return self::LOG_ERRORS_ONLY && substr($httpCode, 0, 1) != 2;
    }

    /**
     * @return array
     */
    private function getFormatted()
    {
        if (!$this->hasFormatted()) {
            $this->formatted = [];
            foreach($this->profilers as $profiler) {
                $this->formatted[$profiler->getName()] = $profiler->getFormatted();
            }
        }
        return $this->formatted;
    }

    /**
     * @return boolean
     */
    private function hasFormatted()
    {
        return $this->formatted !== null;
    }

    /**
     * @return boolean
     */
    private function hasProfilers()
    {
        return !empty($this->profilers);
    }
}
