<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Project cleanup</title>
    <style>
        body { font-family: sans-serif; margin: 2rem; color: #111; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border-bottom: 1px solid #ddd; text-align: left; padding: 0.5rem; }
        .ok { color: #067647; }
        .err { color: #b42318; }
        button { background: #b42318; color: #fff; border: 0; padding: 0.35rem 0.7rem; cursor: pointer; }
    </style>
</head>
<body>
    <h1>Project cleanup</h1>
    <p>Shows projects the normal list hides. Deleting removes the project, its team, tasks, milestones, comments, and files. Quotes and invoices are kept and unlinked.</p>

    @if (session('ok'))
        <p class="ok">{{ session('ok') }}</p>
    @endif
    @if (session('error'))
        <p class="err">{{ session('error') }}</p>
    @endif

    <form method="GET">
        <input type="hidden" name="key" value="{{ $key }}">
        <label>Project id <input type="number" name="id" value="{{ request('id') }}"></label>
        <button type="submit" style="background:#111;">Find</button>
    </form>

    <table>
        <thead>
            <tr>
                <th>Id</th>
                <th>Title</th>
                <th>Status</th>
                <th>Client</th>
                <th>Created</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse ($projects as $project)
                <tr>
                    <td>{{ $project->id }}</td>
                    <td>{{ $project->title }}</td>
                    <td>{{ $project->status }}</td>
                    <td>{{ $project->client->name ?? '—' }}</td>
                    <td>{{ $project->created_at }}</td>
                    <td>
                        <form method="POST" action="{{ route('projects.cleanup.destroy', ['key' => $key]) }}" onsubmit="return confirm('Delete project #{{ $project->id }}?');">
                            @csrf
                            <input type="hidden" name="project_id" value="{{ $project->id }}">
                            <button type="submit">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6">No projects.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
