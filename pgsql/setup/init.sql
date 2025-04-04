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

CREATE SEQUENCE user_id_seq;

CREATE TABLE account (
                uid INTEGER NOT NULL DEFAULT nextval('user_id_seq'),
                role_id SMALLINT NOT NULL,
                full_name VARCHAR(128) NOT NULL,
                document VARCHAR(18) NOT NULL,
                document_type CHAR(1) NOT NULL,
                email VARCHAR(255) NOT NULL,
                password CHAR(97) NOT NULL,
                CONSTRAINT account_pk PRIMARY KEY (uid)
);
COMMENT ON COLUMN account.document_type IS 'F or J';


ALTER SEQUENCE user_id_seq OWNED BY account.uid;

CREATE UNIQUE INDEX user_idx
 ON account
 ( document, document_type );

CREATE UNIQUE INDEX user_idx1
 ON account
 ( email );

CREATE SEQUENCE transfer_id_seq;

CREATE TABLE transfer (
                id INTEGER NOT NULL DEFAULT nextval('transfer_id_seq'),
                payee_id INTEGER NOT NULL,
                payer_id INTEGER NOT NULL,
                identifier CHAR(36) NOT NULL,
                amount NUMERIC(8,2) NOT NULL,
                created TIMESTAMP NOT NULL,
                confirmed TIMESTAMP,
                CONSTRAINT transfer_pk PRIMARY KEY (id)
);


ALTER SEQUENCE transfer_id_seq OWNED BY transfer.id;

CREATE TABLE wallet (
                user_id INTEGER NOT NULL,
                balance NUMERIC(9,2) NOT NULL,
                CONSTRAINT wallet_pk PRIMARY KEY (user_id)
);


ALTER TABLE account ADD CONSTRAINT role_user_fk
FOREIGN KEY (role_id)
REFERENCES account_role (id)
ON DELETE NO ACTION
ON UPDATE NO ACTION
NOT DEFERRABLE;

ALTER TABLE wallet ADD CONSTRAINT user_wallet_fk
FOREIGN KEY (user_id)
REFERENCES account (uid)
ON DELETE NO ACTION
ON UPDATE NO ACTION
NOT DEFERRABLE;

ALTER TABLE transfer ADD CONSTRAINT user_transfer_fk
FOREIGN KEY (payer_id)
REFERENCES account (uid)
ON DELETE NO ACTION
ON UPDATE NO ACTION
NOT DEFERRABLE;

ALTER TABLE transfer ADD CONSTRAINT user_transfer_fk1
FOREIGN KEY (payee_id)
REFERENCES account (uid)
ON DELETE NO ACTION
ON UPDATE NO ACTION
NOT DEFERRABLE;

\c postgres postgres;

ALTER DATABASE ppdbe OWNER TO ppdbe;
