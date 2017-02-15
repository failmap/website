# DDL Mutations
ALTER TABLE faalkaart.coordinate MODIFY area VARCHAR(10000) NOT NULL

ALTER TABLE faalkaart.coordinate ADD geoJsonType VARCHAR(20) DEFAULT "MultiPolygon" NULL;
ALTER TABLE faalkaart.coordinate
  MODIFY COLUMN area VARCHAR(10000) NOT NULL AFTER geoJsonType;



# DML changes

START TRANSACTION;

# Name changes
UPDATE organization set NAME = "Súdwest-Fryslân" WHERE name = "Sudwest Fryslan";
UPDATE URL set Organization = "Súdwest-Fryslân" WHERE organization = "Sudwest Fryslan";
UPDATE coordinate set Organization = "Súdwest-Fryslân" WHERE organization = "Sudwest Fryslan";


UPDATE organization set NAME = "Menameradiel" WHERE name = "Menaldumadeel";
UPDATE URL set Organization = "Menameradiel" WHERE organization = "Menaldumadeel";
UPDATE coordinate set Organization = "Menameradiel" WHERE organization = "Menaldumadeel";

# Merges
UPDATE organization set NAME = "Meierijstad" WHERE name = "Veghel";
UPDATE URL set Organization = "Meierijstad" WHERE organization = "Veghel";
UPDATE coordinate set Organization = "Meierijstad" WHERE organization = "Veghel";


UPDATE URL set Organization = "Meierijstad" WHERE organization = "Schijndel";
UPDATE coordinate set Organization = "Meierijstad" WHERE organization = "Schijndel";

UPDATE URL set Organization = "Meierijstad" WHERE organization = "Sint-Oedenrode";
UPDATE coordinate set Organization = "Meierijstad" WHERE organization = "Sint-Oedenrode";

DELETE FROM organization WHERE name = "Schijndel";
DELETE FROM organization WHERE name = "Sint-Oedenrode";

COMMIT;

# remove all the double dutch (wut) cities. All are now polycoords.
# DELETE FROM coordinate WHERE id = 307;
# DELETE FROM coordinate WHERE id = 315;



