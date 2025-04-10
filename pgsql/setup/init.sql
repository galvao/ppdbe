CREATE ROLE ppdbe WITH CREATEDB LOGIN BYPASSRLS;

\c postgres ppdbe;

CREATE DATABASE ppdbe ENCODING utf8;

\c ppdbe ppdbe;

CREATE SEQUENCE account_role_id_seq;

CREATE TABLE account_role (
                id SMALLINT NOT NULL DEFAULT nextval('account_role_id_seq'),
                label VARCHAR(6) NOT NULL,
                CONSTRAINT account_role_pk PRIMARY KEY (id)
);


ALTER SEQUENCE account_role_id_seq OWNED BY account_role.id;

CREATE SEQUENCE account_id_seq;

CREATE TABLE account (
                id INTEGER NOT NULL DEFAULT nextval('account_id_seq'),
                role_id SMALLINT NOT NULL,
                full_name VARCHAR(128) NOT NULL,
                document VARCHAR(18) NOT NULL,
                document_type CHAR(1) NOT NULL,
                email VARCHAR(255) NOT NULL,
                password CHAR(97) NOT NULL,
                created TIMESTAMP NOT NULL,
                CONSTRAINT account_pk PRIMARY KEY (id)
);
COMMENT ON COLUMN account.document_type IS 'F or J';


ALTER SEQUENCE account_id_seq OWNED BY account.id;

CREATE UNIQUE INDEX user_document_type_uq
 ON account
 ( document, document_type );

CREATE UNIQUE INDEX user_email_uq
 ON account
 ( email );

CREATE SEQUENCE transfer_id_seq;

CREATE TABLE transfer (
                id INTEGER NOT NULL DEFAULT nextval('transfer_id_seq'),
                payer INTEGER NOT NULL,
                payee INTEGER NOT NULL,
                identifier CHAR(36) NOT NULL,
                value NUMERIC(8,2) NOT NULL,
                CONSTRAINT transfer_pk PRIMARY KEY (id)
);


ALTER SEQUENCE transfer_id_seq OWNED BY transfer.id;

CREATE SEQUENCE transfer_log_id_seq;

CREATE TABLE transfer_log (
                id BIGINT NOT NULL DEFAULT nextval('transfer_log_id_seq'),
                transfer_id INTEGER NOT NULL,
                status CHAR(1) NOT NULL,
                retry SMALLINT DEFAULT 3 NOT NULL,
                time_stamp TIMESTAMP NOT NULL,
                CONSTRAINT transfer_log_pk PRIMARY KEY (id)
);
COMMENT ON COLUMN transfer_log.status IS 'Created,Pending,Approved,Failed,Denied,Notified';


ALTER SEQUENCE transfer_log_id_seq OWNED BY transfer_log.id;

CREATE TABLE wallet (
                account_id INTEGER NOT NULL,
                balance NUMERIC(9,2) NOT NULL,
                CONSTRAINT wallet_pk PRIMARY KEY (account_id)
);


ALTER TABLE account ADD CONSTRAINT account_role_account_fk
FOREIGN KEY (role_id)
REFERENCES account_role (id)
ON DELETE NO ACTION
ON UPDATE NO ACTION
NOT DEFERRABLE;

ALTER TABLE wallet ADD CONSTRAINT account_wallet_fk
FOREIGN KEY (account_id)
REFERENCES account (id)
ON DELETE NO ACTION
ON UPDATE NO ACTION
NOT DEFERRABLE;

ALTER TABLE transfer ADD CONSTRAINT account_transfer_payer_fk
FOREIGN KEY (payer)
REFERENCES account (id)
ON DELETE NO ACTION
ON UPDATE NO ACTION
NOT DEFERRABLE;

ALTER TABLE transfer ADD CONSTRAINT account_transfer_payee_fk
FOREIGN KEY (payee)
REFERENCES account (id)
ON DELETE NO ACTION
ON UPDATE NO ACTION
NOT DEFERRABLE;

ALTER TABLE transfer_log ADD CONSTRAINT transfer_transfer_log_fk
FOREIGN KEY (transfer_id)
REFERENCES transfer (id)
ON DELETE NO ACTION
ON UPDATE NO ACTION
NOT DEFERRABLE;

INSERT INTO account_role (label) VALUES
('Vendor'),
('User');

INSERT INTO account (role_id, full_name, document, document_type, email, password, created) VALUES
((SELECT id FROM account_role WHERE label='User'), 'Er Galvão Abbott', '575.225.960-68', 'F', 'galvao@galvao.eti.br', '$argon2id$v=19$m=65536,t=4,p=1$dnh1eDBsRDNra0QyUDJmaA$MZJ0dBC8oPPXNZ9xTZPWxiV9aRQ2AIr9XqSoyarMF5E', (SELECT NOW())),
((SELECT id FROM account_role WHERE label='Vendor'), 'Galvão Desenvolvimento Ltda.', '48.373.511/0001-85', 'J', 'atendimento@galvao.eti.br', '$argon2id$v=19$m=65536,t=4,p=1$akxlVFZVeVRLYmFXU0NCRg$OD6dAiWm7fMZXHJh58xJLqBiA8MpMXyJtvitkzHG+4w', (SELECT NOW()));

UPDATE account SET id = 4 WHERE email='galvao@galvao.eti.br';
UPDATE account SET id = 15 WHERE email='atendimento@galvao.eti.br';

INSERT INTO wallet (account_id, balance) VALUES ((SELECT id FROM account WHERE email='galvao@galvao.eti.br'), 100),
((SELECT id FROM account WHERE email='atendimento@galvao.eti.br'), 0);

\c postgres postgres
ALTER USER ppdbe WITH PASSWORD 'ppdbe1234';
