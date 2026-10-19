-- Add Mathematics wherever Grade 7 is already configured.
-- Safe to run again: existing Mathematics settings are preserved.
INSERT INTO learning_area_settings (division_id, grade_level, learning_area, created_by)
SELECT DISTINCT existing.division_id, 'Grade 7', 'Mathematics', 'system'
FROM learning_area_settings AS existing
WHERE existing.grade_level = 'Grade 7'
  AND NOT EXISTS (
      SELECT 1
      FROM learning_area_settings AS mathematics
      WHERE mathematics.division_id = existing.division_id
        AND mathematics.grade_level = 'Grade 7'
        AND mathematics.learning_area = 'Mathematics'
  );
