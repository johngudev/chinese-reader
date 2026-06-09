<?php

namespace App\Console\Commands;

use CcCedict\Entry;
use CcCedict\Parser;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class LoadCedict extends Command
{
    protected $signature   = 'cedict:load {--path= : Path to the uncompressed CC-CEDICT .txt}';
    protected $description = 'Parse the CC-CEDICT file into the cedict lookup table';

    public function handle(): int
    {
        $path = $this->option('path') ?: storage_path('app/cedict/cedict.txt');

        if (! is_file($path)) {
            $this->error("CC-CEDICT file not found at: {$path}");
            return self::FAILURE;
        }

        $this->info('Clearing existing cedict table...');
        DB::table('cedict')->truncate();              // idempotent: re-run rebuilds cleanly

        $parser = new Parser();
        $parser->setFilePath($path);
        $parser->setOptions([
            Entry::F_SIMPLIFIED,
            Entry::F_TRADITIONAL,
            Entry::F_PINYIN_DIACRITIC,
            Entry::F_PINYIN_NUMERIC,
            Entry::F_ENGLISH,
        ]);
        $parser->setBlockSize(1000);                  // parse + insert 1000 lines per batch

        $total = 0;

        foreach ($parser->parse() as $block) {        // each $block = one batch of parsed lines
            $rows = [];
            foreach ($block['parsedLines'] as $entry) {   // $entry is an assoc array
                $rows[] = [
                    'simplified'     => $entry[Entry::F_SIMPLIFIED],
                    'traditional'    => $entry[Entry::F_TRADITIONAL],
                    'pinyin'         => $entry[Entry::F_PINYIN_DIACRITIC],
                    'pinyin_numeric' => $entry[Entry::F_PINYIN_NUMERIC],
                    'english'        => $entry[Entry::F_ENGLISH],
                ];
            }

            if ($rows) {
                DB::table('cedict')->insert($rows);   // one batched insert per block
                $total += count($rows);
                $this->output->write('.');
            }
        }

        $this->newLine();
        $this->info("Done. Loaded {$total} entries.");

        return self::SUCCESS;
    }
}