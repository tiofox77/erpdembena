-- Modificar o tipo da coluna downtime_length de varchar para decimal(10,2)
-- Esta alteração é necessária para permitir operações matemáticas como SUM
ALTER TABLE maintenance_downtimes
MODIFY COLUMN downtime_length DECIMAL(10,2);

-- Se precisar reverter a alteração
-- ALTER TABLE maintenance_downtimes
-- MODIFY COLUMN downtime_length VARCHAR(255);
