<?php

namespace De\Idrinth\S3Duplication;

final class LocalUploader implements Uploader
{
    private string $path;
    private string $user;
    private string $group;
    
    public function __construct(string $path, string $user, string $group)
    {
        $this->path = $path;
        $this->user = $user;
        $this->group = $group;
        mkdir($this->path, 0777, true);
    }

    public function put(string $path, string $data): void
    {
        file_put_contents($this->path . $path, $data);
        chgrp($this->path . $path, $this->group);
        chown($this->path . $path, $this->user);
    }

    private function scan(string $directory, array &$output): void
    {
        if (!is_dir($directory)) {
            return;
        }
        foreach (array_diff(scandir($directory), ['.', '..']) as $file) {
            if (is_dir($directory . '/' . $file)) {
                $this->scan($directory . '/' . $file, $output);
            } else {
                $output[] = preg_replace('/^' . preg_quote($this->path, '/') . '/', '', $directory . '/' . $file);
            }
        }
    }

    public function list(): array
    {
        echo "  Getting objects from target {$this->path}\n";
        $output = [];
        $this->scan($this->path, $output);
        echo "    Found " . count($output) . " objects.\n";
        return $output;
    }
}
