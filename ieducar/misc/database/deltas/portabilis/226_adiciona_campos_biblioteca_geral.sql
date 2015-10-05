--
-- @author   Alan Felipe Farias <alan@portabilis.com.br>
-- @license  @@license@@
-- @version  $Id$


 ALTER TABLE pmieducar.acervo ADD COLUMN dimencao VARCHAR(255);
 ALTER TABLE pmieducar.acervo ADD COLUMN material_ilustrativo VARCHAR(255);
 ALTER TABLE pmieducar.acervo ADD COLUMN dimencao_ilustrativo VARCHAR(255);
 ALTER TABLE pmieducar.acervo ADD COLUMN local VARCHAR(255);
 ALTER TABLE pmieducar.acervo ALTER COLUMN isbn type VARCHAR(255);
 ALTER TABLE pmieducar.acervo ADD COLUMN ref_cod_tipo_autor int;
 ALTER TABLE pmieducar.acervo ADD COLUMN tipo_autor VARCHAR(255);
CREATE TABLE pmieducar.tipo_autor(codigo int,tipo_autor varchar(255));
INSERT INTO pmieducar.tipo_autor VALUES (1, 'Autor');
INSERT INTO pmieducar.tipo_autor VALUES (2, 'Evento');
INSERT INTO pmieducar.tipo_autor VALUES (3, 'Entidade coletiva');
INSERT INTO pmieducar.tipo_autor VALUES (4, 'Anônimo');

 --undo

 ALTER TABLE pmieducar.acervo DROP COLUMN dimencao;
 ALTER TABLE pmieducar.acervo DROP COLUMN material_ilustrativo;
 ALTER TABLE pmieducar.acervo DROP COLUMN dimencao_ilustrativo;
 ALTER TABLE pmieducar.acervo DROP COLUMN local;
 ALTER TABLE pmieducar.acervo DROP COLUMN ref_cod_tipoautor;
  ALTER TABLE pmieducar.acervo ALTER COLUMN isbn type int;
  DROP TABLE pmieducar.tipo_autor;