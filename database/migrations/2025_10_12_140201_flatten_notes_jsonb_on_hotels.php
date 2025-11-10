<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // [{ "text": "..." }] -> ["..."]
        $sql = "
        UPDATE hotels
        SET notes = (
          SELECT jsonb_object_agg(k,
            CASE
              WHEN jsonb_typeof(v) = 'array' THEN (
                SELECT jsonb_agg(
                  CASE
                    WHEN jsonb_typeof(e) = 'object' THEN to_jsonb(e->>'text')
                    ELSE e
                  END
                )
                FROM jsonb_array_elements(v) AS e
              )
              ELSE v
            END
          )
          FROM jsonb_each(COALESCE(hotels.notes, '{}'::jsonb)) AS t(k, v)
        )
        WHERE notes IS NOT NULL;
        ";

        DB::statement($sql);
    }

    public function down(): void
    {
        // ["..."] -> [{ "text": "..." }]
        $sql = "
        UPDATE hotels
        SET notes = (
          SELECT jsonb_object_agg(k,
            CASE
              WHEN jsonb_typeof(v) = 'array' THEN (
                SELECT jsonb_agg(
                  jsonb_build_object('text', btrim(e::text, '\"'))
                )
                FROM jsonb_array_elements(v) AS e
              )
              ELSE v
            END
          )
          FROM jsonb_each(COALESCE(hotels.notes, '{}'::jsonb)) AS t(k, v)
        )
        WHERE notes IS NOT NULL;
        ";

        DB::statement($sql);
    }
};
