<?php

class Hunspell 
{

    const OK = '*';
    const ROOT = '+';
    const MISS = '&';
    const NONE = '#';
    const COMPOUND = '-';
    const STATUSES_NAME = [
        Hunspell::OK => 'OK',
        Hunspell::ROOT => 'ROOT',
        Hunspell::MISS => 'MISS',
        Hunspell::NONE => 'NONE',
        Hunspell::COMPOUND => 'COMPOUND',
    ];

    protected $matcher =
        "/(?P<type>\*|\+|&|#)\s?(?P<original>\w+)?\s?(?P<count>\d+)?\s?(?P<offset>\d+)?:?\s?(?P<misses>.*+)?/u";

    protected function sendCommand($input) 
    {

        $descriptors = array 
        (
            0 => array("pipe", "r"),  // STDIN
            1 => array("pipe", "w"),  // STDOUT
            2 => array("pipe", "w")   // STDERR
        );

        $env = array
        (
            'LANG' => 'uz_UZ.utf-8'
        );

        $process = proc_open('hunspell -d uz_UZ', $descriptors, $pipes, NULL, $env);
        if (!is_resource($process)) 
        {
            die("Could not start Hunspell!");
        }

        $stdIn = &$pipes[0];
        $stdOut = &$pipes[1];
        $stdErr = &$pipes[2];
        unset($pipes);

        fwrite($stdIn, $input);
        fclose($stdIn);

        $result = "";
        while (!feof($stdOut)) $result = $result . fgets($stdOut);
        fclose($stdOut);

        $error = "";
        while (!feof($stdErr)) $error = $error . fgets($stdErr);
        fclose($stdErr);

        proc_close($process);

        return $result;
    }

    public function spell($input)
    {

        $result = $this->sendCommand($input);

        $matches = [];
        $results = $this->preParse($result, $input);
        $response = [];
        foreach ($results as $word => $result) 
        {
            $matches = [];
            $match = preg_match($this->matcher, $result, $matches);
            $matches['input'] = $word;
            if ($matches['type'] == Hunspell::MISS) $response[] = $matches["original"]; 
        }

        return $response;
    }

    public function suggest($input)
    {
        $result = $this->sendCommand($input);

        $matches = [];
        $results = $this->preParse($result, $input);
        $response = [];
        foreach ($results as $word => $result) 
        {
            $matches = [];
            $match = preg_match($this->matcher, $result, $matches);
            $matches['input'] = $word;
            if ($matches['type'] == Hunspell::MISS) 
            {
                $response[] = explode(", ", $matches['misses']);
            }
        }

        return $response;
    }

    protected function preParse($input, $words)
    {
        $result = explode(PHP_EOL, trim($input));
        unset($result[0]);
        $words = array_map('trim', explode(" ", $words));
        return array_combine($words, $result);
    }

}

?>
