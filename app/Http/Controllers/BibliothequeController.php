<?php

namespace App\Http\Controllers;

use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BibliothequeController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        $livres = DB::select("
            SELECT
                l.isbn,
                l.titre,
                l.edition,
                l.nombre_de_page,
                l.id_abonne,
                CASE WHEN l.id_abonne IS NULL THEN 'Disponible' ELSE 'Emprunté' END AS statut,
                p_ab.nom AS emprunteur_nom,
                p_ab.prenoms AS emprunteur_prenoms,
                COALESCE(
                    STRING_AGG(DISTINCT p_au.nom || ' ' || p_au.prenoms, ', ' ORDER BY p_au.nom || ' ' || p_au.prenoms),
                    'Aucun auteur'
                ) AS auteurs,
                COALESCE(
                    STRING_AGG(DISTINCT e.id_personne::text, ','),
                    ''
                ) AS auteur_ids
            FROM livre l
            LEFT JOIN ecrire e ON l.isbn = e.isbn
            LEFT JOIN personne p_au ON e.id_personne = p_au.id_personne
            LEFT JOIN abonne a ON l.id_abonne = a.id_abonne
            LEFT JOIN personne p_ab ON a.id_personne = p_ab.id_personne
            WHERE :q = ''
               OR l.isbn ILIKE :motif
               OR l.titre ILIKE :motif
               OR COALESCE(l.edition, '') ILIKE :motif
               OR p_au.nom ILIKE :motif
               OR p_au.prenoms ILIKE :motif
            GROUP BY l.isbn, l.titre, l.edition, l.nombre_de_page, l.id_abonne, p_ab.nom, p_ab.prenoms
            ORDER BY l.titre ASC
        ", [
            'q'     => $q,
            'motif' => '%' . $q . '%',
        ]);

        $personnes = DB::table('personne')
            ->orderBy('nom')
            ->orderBy('prenoms')
            ->get();

        $abonnes = DB::table('abonne as a')
            ->join('personne as p', 'a.id_personne', '=', 'p.id_personne')
            ->select('a.*', 'p.nom', 'p.prenoms')
            ->orderBy('p.nom')
            ->orderBy('p.prenoms')
            ->get();

        return view('bibliotheque.index', compact('livres', 'personnes', 'abonnes', 'q'));
    }

    public function storeLivre(Request $request)
    {
        $data = $request->validate([
            'isbn'           => ['required', 'string', 'max:20'],
            'titre'          => ['required', 'string', 'max:255'],
            'edition'        => ['nullable', 'string', 'max:120'],
            'nombre_de_page' => ['required', 'integer', 'min:1'],
            'id_abonne'      => ['nullable', 'integer', 'exists:abonne,id_abonne'],
            'auteurs'        => ['nullable', 'array'],
            'auteurs.*'      => ['integer', 'exists:personne,id_personne'],
        ]);

        try {
            DB::transaction(function () use ($data) {
                $isbn = trim($data['isbn']);

                DB::table('livre')->insert([
                    'isbn'           => $isbn,
                    'titre'          => trim($data['titre']),
                    'edition'        => !empty($data['edition']) ? trim($data['edition']) : null,
                    'nombre_de_page' => (int) $data['nombre_de_page'],
                    'id_abonne'      => $data['id_abonne'] ?? null,
                ]);

                foreach (array_unique($data['auteurs'] ?? []) as $idPersonne) {
                    DB::table('ecrire')->insert([
                        'isbn'        => $isbn,
                        'id_personne' => (int) $idPersonne,
                    ]);
                }
            });

            return redirect()->route('bibliotheque.index')->with('success', 'Livre ajouté avec succès.');
        } catch (QueryException $e) {
            return back()->withInput()->with('error', $this->messageErreurPg($e));
        }
    }

    public function updateLivre(Request $request, string $isbn)
    {
        $data = $request->validate([
            'titre'          => ['required', 'string', 'max:255'],
            'edition'        => ['nullable', 'string', 'max:120'],
            'nombre_de_page' => ['required', 'integer', 'min:1'],
            'id_abonne'      => ['nullable', 'integer', 'exists:abonne,id_abonne'],
            'auteurs'        => ['nullable', 'array'],
            'auteurs.*'      => ['integer', 'exists:personne,id_personne'],
        ]);

        try {
            DB::transaction(function () use ($data, $isbn) {
                DB::table('livre')
                    ->where('isbn', $isbn)
                    ->update([
                        'titre'          => trim($data['titre']),
                        'edition'        => !empty($data['edition']) ? trim($data['edition']) : null,
                        'nombre_de_page' => (int) $data['nombre_de_page'],
                        'id_abonne'      => $data['id_abonne'] ?? null,
                    ]);

                DB::table('ecrire')->where('isbn', $isbn)->delete();

                foreach (array_unique($data['auteurs'] ?? []) as $idPersonne) {
                    DB::table('ecrire')->insert([
                        'isbn'        => $isbn,
                        'id_personne' => (int) $idPersonne,
                    ]);
                }
            });

            return redirect()->route('bibliotheque.index')->with('success', 'Livre et auteur(s) mis à jour.');
        } catch (QueryException $e) {
            return back()->withInput()->with('error', $this->messageErreurPg($e));
        }
    }

    public function destroyLivre(string $isbn)
    {
        try {
            DB::table('livre')->where('isbn', $isbn)->delete();
            return redirect()->route('bibliotheque.index')->with('success', 'Livre supprimé.');
        } catch (QueryException $e) {
            return back()->with('error', $this->messageErreurPg($e));
        }
    }

    public function storePersonne(Request $request)
    {
        $data = $request->validate([
            'nom'               => ['required', 'string', 'max:100'],
            'prenoms'           => ['required', 'string', 'max:150'],
            'date_de_naissance' => ['nullable', 'date'],
        ]);

        try {
            DB::table('personne')->insert([
                'nom'               => trim($data['nom']),
                'prenoms'           => trim($data['prenoms']),
                'date_de_naissance' => $data['date_de_naissance'] ?? null,
            ]);

            return redirect()->route('bibliotheque.index')->with('success', 'Personne ajoutée.');
        } catch (QueryException $e) {
            return back()->withInput()->with('error', $this->messageErreurPg($e));
        }
    }

    public function updatePersonne(Request $request, int $idPersonne)
    {
        $data = $request->validate([
            'nom'               => ['required', 'string', 'max:100'],
            'prenoms'           => ['required', 'string', 'max:150'],
            'date_de_naissance' => ['nullable', 'date'],
        ]);

        try {
            DB::table('personne')
                ->where('id_personne', $idPersonne)
                ->update([
                    'nom'               => trim($data['nom']),
                    'prenoms'           => trim($data['prenoms']),
                    'date_de_naissance' => $data['date_de_naissance'] ?? null,
                ]);

            return redirect()->route('bibliotheque.index')->with('success', 'Personne mise à jour.');
        } catch (QueryException $e) {
            return back()->withInput()->with('error', $this->messageErreurPg($e));
        }
    }

    public function destroyPersonne(int $idPersonne)
    {
        try {
            DB::table('personne')->where('id_personne', $idPersonne)->delete();
            return redirect()->route('bibliotheque.index')->with('success', 'Personne supprimée.');
        } catch (QueryException $e) {
            return back()->with('error', $this->messageErreurPg($e));
        }
    }

    public function storeAbonne(Request $request)
    {
        $data = $request->validate([
            'id_personne'  => ['required', 'integer', 'exists:personne,id_personne'],
            'profession'   => ['nullable', 'string', 'max:120'],
            'institut'     => ['nullable', 'string', 'max:150'],
            'lot'          => ['nullable', 'string', 'max:100'],
            'ville'        => ['required', 'string', 'max:100'],
            'code_postale' => ['nullable', 'string', 'max:20'],
        ]);

        try {
            DB::table('abonne')->insert([
                'id_personne'  => (int) $data['id_personne'],
                'profession'   => !empty($data['profession']) ? trim($data['profession']) : null,
                'institut'     => !empty($data['institut']) ? trim($data['institut']) : null,
                'lot'          => !empty($data['lot']) ? trim($data['lot']) : null,
                'ville'        => trim($data['ville']),
                'code_postale' => !empty($data['code_postale']) ? trim($data['code_postale']) : null,
            ]);

            return redirect()->route('bibliotheque.index')->with('success', 'Abonné enregistré.');
        } catch (QueryException $e) {
            return back()->withInput()->with('error', $this->messageErreurPg($e));
        }
    }

    public function updateAbonne(Request $request, int $idAbonne)
    {
        $data = $request->validate([
            'profession'   => ['nullable', 'string', 'max:120'],
            'institut'     => ['nullable', 'string', 'max:150'],
            'lot'          => ['nullable', 'string', 'max:100'],
            'ville'        => ['required', 'string', 'max:100'],
            'code_postale' => ['nullable', 'string', 'max:20'],
        ]);

        try {
            DB::table('abonne')
                ->where('id_abonne', $idAbonne)
                ->update([
                    'profession'   => !empty($data['profession']) ? trim($data['profession']) : null,
                    'institut'     => !empty($data['institut']) ? trim($data['institut']) : null,
                    'lot'          => !empty($data['lot']) ? trim($data['lot']) : null,
                    'ville'        => trim($data['ville']),
                    'code_postale' => !empty($data['code_postale']) ? trim($data['code_postale']) : null,
                ]);

            return redirect()->route('bibliotheque.index')->with('success', 'Abonné mis à jour.');
        } catch (QueryException $e) {
            return back()->withInput()->with('error', $this->messageErreurPg($e));
        }
    }

    public function destroyAbonne(int $idAbonne)
    {
        try {
            DB::table('abonne')->where('id_abonne', $idAbonne)->delete();
            return redirect()->route('bibliotheque.index')->with('success', 'Abonné supprimé.');
        } catch (QueryException $e) {
            return back()->with('error', $this->messageErreurPg($e));
        }
    }

    private function messageErreurPg(QueryException $e): string
    {
        $code = $e->getCode();

        return match ($code) {
            '23505' => 'Doublon détecté : cet ISBN ou cette personne (déjà abonné / auteur) existe déjà.',
            '23503' => 'Contrainte de clé étrangère : cet élément est encore référencé (livre emprunté, abonné ou auteur lié).',
            '23514' => 'Contrainte non respectée : vérifiez les valeurs (ex. nombre de pages supérieur à 0).',
            '23502' => 'Champ obligatoire manquant.',
            default => 'Erreur PostgreSQL (' . $code . ') : ' . $e->getMessage(),
        };
    }
}
