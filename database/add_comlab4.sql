-- Select the existing techpulse monitoring database before running this update.
-- Existing computers, maintenance statuses, accounts, and sessions are preserved.
START TRANSACTION;

INSERT INTO laboratories (lab_id, lab_name)
SELECT 4, 'ComLab 4'
WHERE NOT EXISTS (SELECT 1 FROM laboratories WHERE lab_id = 4);

INSERT INTO computers (lab_id, pc_number, status)
SELECT 4, numbers.pc_number, 'available'
FROM (
    SELECT tens.n * 10 + ones.n + 1 AS pc_number
    FROM (SELECT 0 AS n UNION ALL SELECT 1 UNION ALL SELECT 2
          UNION ALL SELECT 3 UNION ALL SELECT 4) AS tens
    CROSS JOIN (SELECT 0 AS n UNION ALL SELECT 1 UNION ALL SELECT 2
                UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5
                UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8
                UNION ALL SELECT 9) AS ones
) AS numbers
WHERE numbers.pc_number <= 45
  AND NOT EXISTS (
      SELECT 1 FROM computers c
      WHERE c.lab_id = 4 AND c.pc_number = numbers.pc_number
  )
ORDER BY numbers.pc_number;

COMMIT;
