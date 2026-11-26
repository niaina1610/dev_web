<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion de Bibliothèque</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root {
            --bg-page: #f1f5f9;
            --bg-card: #ffffff;
            --border: #cbd5e1;
            --text: #1e293b;
            --muted: #64748b;
        }

        body {
            font-family: system-ui, -apple-system, sans-serif;
            background-color: var(--bg-page);
            color: var(--text);
            max-width: 1260px;
            margin: 24px auto;
            padding: 0 16px;
        }

        h1 {
            color: #0f172a;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        h2, h3 {
            margin-top: 0;
            color: #0f172a;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .card {
            background: var(--bg-card);
            border-radius: 10px;
            padding: 18px;
            box-shadow: 0 2px 8px rgba(15, 23, 42, 0.06);
            margin-bottom: 22px;
        }

        .search-bar {
            display: flex;
            gap: 10px;
            align-items: center;
            flex-wrap: wrap;
        }

        .search-bar input {
            flex: 1;
            min-width: 220px;
            margin: 0;
        }

        .reset-link {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            color: #475569;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
        }

        .reset-link:hover {
            color: #0f172a;
        }

        .table-wrapper {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            border-radius: 8px;
            overflow: hidden;
        }

        th, td {
            padding: 10px 12px;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
            vertical-align: top;
        }

        /* Table Livres : Bleu */
        .table-livres thead { background-color: #1e3a8a; color: #ffffff; }
        .table-livres tbody tr:nth-child(odd) { background-color: #ffffff; }
        .table-livres tbody tr:nth-child(even) { background-color: #eff6ff; }
        .table-livres tbody tr:hover { background-color: #dbeafe; }

        /* Table Personnes : Sarcelle / Vert */
        .table-personnes thead { background-color: #0f766e; color: #ffffff; }
        .table-personnes tbody tr:nth-child(odd) { background-color: #ffffff; }
        .table-personnes tbody tr:nth-child(even) { background-color: #f0fdfa; }
        .table-personnes tbody tr:hover { background-color: #ccfbf1; }

        /* Table Abonnés : Violet */
        .table-abonnes thead { background-color: #5b21b6; color: #ffffff; }
        .table-abonnes tbody tr:nth-child(odd) { background-color: #ffffff; }
        .table-abonnes tbody tr:nth-child(even) { background-color: #f5f3ff; }
        .table-abonnes tbody tr:hover { background-color: #ede9fe; }

        .badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 4px 9px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 600;
        }

        .badge-dispo {
            background-color: #dcfce7;
            color: #166534;
        }

        .badge-emprunt {
            background-color: #fef3c7;
            color: #92400e;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(310px, 1fr));
            gap: 18px;
        }

        .box-livre { border-top: 4px solid #1e3a8a; }
        .box-personne { border-top: 4px solid #0f766e; }
        .box-abonne { border-top: 4px solid #5b21b6; }

        label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: var(--muted);
            margin-top: 4px;
        }

        input, select {
            width: 100%;
            margin: 4px 0 10px;
            padding: 7px 9px;
            border: 1px solid var(--border);
            border-radius: 6px;
            background-color: #ffffff;
            box-sizing: border-box;
        }

        input:focus, select:focus {
            outline: none;
            border-color: #2563eb;
        }

        button {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background-color: #1e3a8a;
            color: #ffffff;
            border: none;
            border-radius: 6px;
            padding: 7px 12px;
            font-weight: 600;
            cursor: pointer;
        }

        button:hover {
            opacity: 0.92;
        }

        .btn-teal { background-color: #0f766e; }
        .btn-violet { background-color: #5b21b6; }

        .btn-danger {
            background-color: #dc2626;
            margin-top: 6px;
        }

        .btn-danger:hover {
            background-color: #b91c1c;
        }

        .ok {
            display: flex;
            align-items: center;
            gap: 8px;
            background: #dcfce7;
            border-left: 4px solid #16a34a;
            color: #14532d;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 14px;
        }

        .err {
            background: #fee2e2;
            border-left: 4px solid #dc2626;
            color: #7f1d1d;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 14px;
        }
    </style>
</head>
<body>
    <h1><i class="bi bi-journal-bookmark-fill" style="color:#1e3a8a;"></i> Gestion de Bibliothèque</h1>

    @if(session('success'))
        <div class="ok"><i class="bi bi-check-circle-fill"></i> <span>{{ session('success') }}</span></div>
    @endif

    @if(session('error'))
        <div class="err"><i class="bi bi-exclamation-triangle-fill"></i> {{ session('error') }}</div>
    @endif

    @if($errors->any())
        <div class="err">
            <ul>
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card">
        <form class="search-bar" method="GET" action="{{ route('bibliotheque.index') }}">
            <input type="text" name="q" value="{{ $q }}" placeholder="Rechercher par ISBN, titre, édition ou auteur...">
            <button type="submit"><i class="bi bi-search"></i> Rechercher</button>
            @if($q !== '')
                <a href="{{ route('bibliotheque.index') }}" class="reset-link"><i class="bi bi-x-circle"></i> Réinitialiser</a>
            @endif
        </form>
    </div>

    <!-- TABLE LIVRES -->
    <div class="card">
        <h2><i class="bi bi-book" style="color:#1e3a8a;"></i> Livres ({{ count($livres) }})</h2>
        <div class="table-wrapper">
            <table class="table-livres">
                <thead>
                    <tr>
                        <th>ISBN</th>
                        <th>Titre</th>
                        <th>Édition</th>
                        <th>Pages</th>
                        <th>Auteurs</th>
                        <th>Statut / Emprunteur</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($livres as $livre)
                        @php
                            $auteurIds = array_values(array_filter(explode(',', (string) ($livre->auteur_ids ?? ''))));
                        @endphp
                        <tr>
                            <td><strong>{{ $livre->isbn }}</strong></td>
                            <td>{{ $livre->titre }}</td>
                            <td>{{ $livre->edition ?? '—' }}</td>
                            <td>{{ $livre->nombre_de_page }}</td>
                            <td>{{ $livre->auteurs }}</td>
                            <td>
                                @if($livre->id_abonne === null)
                                    <span class="badge badge-dispo"><i class="bi bi-check-circle"></i> Disponible</span>
                                @else
                                    <span class="badge badge-emprunt"><i class="bi bi-bookmark-check"></i> Emprunté</span>
                                    @if($livre->emprunteur_nom)
                                        <div style="margin-top:4px; font-size:13px;">
                                            <i class="bi bi-person"></i> {{ $livre->emprunteur_nom }} {{ $livre->emprunteur_prenoms }}
                                        </div>
                                    @endif
                                @endif
                            </td>
                            <td>
                                <form method="POST" action="{{ route('livres.update', $livre->isbn) }}">
                                    @csrf
                                    @method('PUT')

                                    <label>Titre</label>
                                    <input type="text" name="titre" value="{{ $livre->titre }}" required>

                                    <label>Édition</label>
                                    <input type="text" name="edition" value="{{ $livre->edition }}" placeholder="Édition">

                                    <label>Pages</label>
                                    <input type="number" name="nombre_de_page" value="{{ $livre->nombre_de_page }}" min="1" required>

                                    <label>Auteur(s) (Ctrl / Cmd + clic)</label>
                                    <select name="auteurs[]" multiple size="3">
                                        @foreach($personnes as $p)
                                            <option value="{{ $p->id_personne }}" @selected(in_array((string) $p->id_personne, $auteurIds, true))>
                                                {{ $p->nom }} {{ $p->prenoms }}
                                            </option>
                                        @endforeach
                                    </select>

                                    <label>Emprunteur</label>
                                    <select name="id_abonne">
                                        <option value="">Disponible</option>
                                        @foreach($abonnes as $ab)
                                            <option value="{{ $ab->id_abonne }}" @selected($livre->id_abonne == $ab->id_abonne)>
                                                {{ $ab->nom }} {{ $ab->prenoms }} ({{ $ab->profession ?? 'Abonné' }})
                                            </option>
                                        @endforeach
                                    </select>

                                    <button type="submit"><i class="bi bi-pencil-square"></i> Modifier</button>
                                </form>

                                <form method="POST" action="{{ route('livres.destroy', $livre->isbn) }}" onsubmit="return confirm('Supprimer ce livre ?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-danger"><i class="bi bi-trash"></i> Supprimer</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7">Aucun livre trouvé.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- TABLE PERSONNES -->
    <div class="card">
        <h2><i class="bi bi-people" style="color:#0f766e;"></i> Personnes ({{ count($personnes) }})</h2>
        <div class="table-wrapper">
            <table class="table-personnes">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nom</th>
                        <th>Prénoms</th>
                        <th>Date de naissance</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($personnes as $p)
                        <tr>
                            <td><strong>#{{ $p->id_personne }}</strong></td>
                            <td>{{ $p->nom }}</td>
                            <td>{{ $p->prenoms }}</td>
                            <td>{{ $p->date_de_naissance ?? '—' }}</td>
                            <td>
                                <form method="POST" action="{{ route('personnes.update', $p->id_personne) }}">
                                    @csrf
                                    @method('PUT')
                                    <input type="text" name="nom" value="{{ $p->nom }}" required>
                                    <input type="text" name="prenoms" value="{{ $p->prenoms }}" required>
                                    <input type="date" name="date_de_naissance" value="{{ $p->date_de_naissance }}">
                                    <button type="submit" class="btn-teal"><i class="bi bi-pencil-square"></i> Modifier</button>
                                </form>

                                <form method="POST" action="{{ route('personnes.destroy', $p->id_personne) }}" onsubmit="return confirm('Supprimer cette personne ?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-danger"><i class="bi bi-trash"></i> Supprimer</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5">Aucune personne enregistrée.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- TABLE ABONNÉS -->
    <div class="card">
        <h2><i class="bi bi-person-badge" style="color:#5b21b6;"></i> Abonnés ({{ count($abonnes) }})</h2>
        <div class="table-wrapper">
            <table class="table-abonnes">
                <thead>
                    <tr>
                        <th>ID Abonné</th>
                        <th>Personne</th>
                        <th>Profession</th>
                        <th>Institut</th>
                        <th>Lot</th>
                        <th>Ville</th>
                        <th>Code postal</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($abonnes as $ab)
                        <tr>
                            <td><strong>#{{ $ab->id_abonne }}</strong></td>
                            <td>{{ $ab->nom }} {{ $ab->prenoms }}</td>
                            <td>{{ $ab->profession ?? '—' }}</td>
                            <td>{{ $ab->institut ?? '—' }}</td>
                            <td>{{ $ab->lot ?? '—' }}</td>
                            <td>{{ $ab->ville }}</td>
                            <td>{{ $ab->code_postale ?? '—' }}</td>
                            <td>
                                <form method="POST" action="{{ route('abonnes.update', $ab->id_abonne) }}">
                                    @csrf
                                    @method('PUT')
                                    <input type="text" name="profession" value="{{ $ab->profession }}" placeholder="Profession">
                                    <input type="text" name="institut" value="{{ $ab->institut }}" placeholder="Institut">
                                    <input type="text" name="lot" value="{{ $ab->lot }}" placeholder="Lot">
                                    <input type="text" name="ville" value="{{ $ab->ville }}" required>
                                    <input type="text" name="code_postale" value="{{ $ab->code_postale }}" placeholder="Code postal">
                                    <button type="submit" class="btn-violet"><i class="bi bi-pencil-square"></i> Modifier</button>
                                </form>

                                <form method="POST" action="{{ route('abonnes.destroy', $ab->id_abonne) }}" onsubmit="return confirm('Supprimer cet abonné ?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-danger"><i class="bi bi-trash"></i> Supprimer</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8">Aucun abonné enregistré.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- FORMULAIRES D'AJOUT -->
    <div class="grid">
        <form class="card box-livre" method="POST" action="{{ route('livres.store') }}">
            @csrf
            <h3><i class="bi bi-journal-plus" style="color:#1e3a8a;"></i> Ajouter un livre</h3>
            <label>ISBN</label>
            <input type="text" name="isbn" value="{{ old('isbn') }}" required>
            <label>Titre</label>
            <input type="text" name="titre" value="{{ old('titre') }}" required>
            <label>Édition</label>
            <input type="text" name="edition" value="{{ old('edition') }}">
            <label>Nombre de pages</label>
            <input type="number" name="nombre_de_page" value="{{ old('nombre_de_page', 1) }}" min="1" required>
            <label>Auteur(s)</label>
            <select name="auteurs[]" multiple size="3">
                @foreach($personnes as $p)
                    <option value="{{ $p->id_personne }}">{{ $p->nom }} {{ $p->prenoms }}</option>
                @endforeach
            </select>
            <label>Emprunté par (optionnel)</label>
            <select name="id_abonne">
                <option value="">Aucun (Disponible)</option>
                @foreach($abonnes as $ab)
                    <option value="{{ $ab->id_abonne }}">{{ $ab->nom }} {{ $ab->prenoms }}</option>
                @endforeach
            </select>
            <button type="submit"><i class="bi bi-plus-circle"></i> Ajouter le livre</button>
        </form>

        <form class="card box-personne" method="POST" action="{{ route('personnes.store') }}">
            @csrf
            <h3><i class="bi bi-person-plus" style="color:#0f766e;"></i> Ajouter une personne</h3>
            <label>Nom</label>
            <input type="text" name="nom" value="{{ old('nom') }}" required>
            <label>Prénoms</label>
            <input type="text" name="prenoms" value="{{ old('prenoms') }}" required>
            <label>Date de naissance</label>
            <input type="date" name="date_de_naissance" value="{{ old('date_de_naissance') }}">
            <button type="submit" class="btn-teal"><i class="bi bi-plus-circle"></i> Enregistrer la personne</button>
        </form>

        <form class="card box-abonne" method="POST" action="{{ route('abonnes.store') }}">
            @csrf
            <h3><i class="bi bi-person-lines-fill" style="color:#5b21b6;"></i> Ajouter un abonné</h3>
            <label>Personne</label>
            <select name="id_personne" required>
                @foreach($personnes as $p)
                    <option value="{{ $p->id_personne }}">{{ $p->nom }} {{ $p->prenoms }}</option>
                @endforeach
            </select>
            <label>Profession</label>
            <input type="text" name="profession" value="{{ old('profession') }}">
            <label>Institut</label>
            <input type="text" name="institut" value="{{ old('institut') }}">
            <label>Lot</label>
            <input type="text" name="lot" value="{{ old('lot') }}">
            <label>Ville</label>
            <input type="text" name="ville" value="{{ old('ville') }}" required>
            <label>Code postal</label>
            <input type="text" name="code_postale" value="{{ old('code_postale') }}">
            <button type="submit" class="btn-violet"><i class="bi bi-plus-circle"></i> Créer l'abonné</button>
        </form>
    </div>
</body>
</html>
