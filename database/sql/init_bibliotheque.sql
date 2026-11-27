CREATE TABLE personne (
    id_personne INT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    prenoms VARCHAR(150) NOT NULL,
    date_de_naissance DATE
);

CREATE TABLE abonne (
    id_abonne INT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    profession VARCHAR(120),
    institut VARCHAR(150),
    lot VARCHAR(100),
    ville VARCHAR(100) NOT NULL,
    code_postale VARCHAR(20),
    id_personne INT NOT NULL UNIQUE,
    CONSTRAINT fk_abonne_personne
        FOREIGN KEY (id_personne) REFERENCES personne(id_personne)
        ON DELETE CASCADE
        ON UPDATE CASCADE
);

CREATE TABLE livre (
    isbn VARCHAR(20) PRIMARY KEY,
    titre VARCHAR(255) NOT NULL,
    edition VARCHAR(120),
    nombre_de_page INT NOT NULL CHECK (nombre_de_page > 0),
    id_abonne INT NULL,
    CONSTRAINT fk_livre_abonne
        FOREIGN KEY (id_abonne) REFERENCES abonne(id_abonne)
        ON DELETE SET NULL
        ON UPDATE CASCADE
);

CREATE TABLE ecrire (
    isbn VARCHAR(20) NOT NULL,
    id_personne INT NOT NULL,
    PRIMARY KEY (isbn, id_personne),
    CONSTRAINT fk_ecrire_livre
        FOREIGN KEY (isbn) REFERENCES livre(isbn)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    CONSTRAINT fk_ecrire_personne
        FOREIGN KEY (id_personne) REFERENCES personne(id_personne)
        ON DELETE CASCADE
        ON UPDATE CASCADE
);

INSERT INTO personne (nom, prenoms, date_de_naissance) VALUES
('Rakoto', 'Jean Luc', '1980-05-14'),
('Niaina', 'Roberte', '2003-10-16');

INSERT INTO abonne (profession, institut, lot, ville, code_postale, id_personne) VALUES
('Étudiant', 'ISPM', 'Lot II M 45', 'Antananarivo', '101', 2);

INSERT INTO livre (isbn, titre, edition, nombre_de_page, id_abonne) VALUES
('978-2-212-12345-6', 'Introduction aux bases de données', 'Eyrolles', 320, NULL),
('978-2-10-078910-2', 'Algorithmique pratique', 'Dunod', 410, 1);

INSERT INTO ecrire (isbn, id_personne) VALUES
('978-2-212-12345-6', 1),
('978-2-10-078910-2', 1);
