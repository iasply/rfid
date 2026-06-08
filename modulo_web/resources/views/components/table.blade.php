@props(['headers'])

<div class="table-container">
    <table class="premium-table">
        <thead>
        <tr>
            @foreach($headers as $header)
                <th class="{{ strtolower($header) == 'ações' ? 'text-right' : '' }}">
                    {{ $header }}
                </th>
            @endforeach
        </tr>
        </thead>
        <tbody>
        {{ $slot }}
        </tbody>
    </table>
</div>

<style>
    .table-container {
        width: 100%;
        min-width: 0;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        border-radius: var(--radius-md);
        position: relative;
    }

    /* Scrollbar Styling for Premium Feel */
    .table-container::-webkit-scrollbar {
        height: 6px;
    }

    .table-container::-webkit-scrollbar-track {
        background: var(--bg-main);
    }

    .table-container::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 3px;
    }

    .table-container::-webkit-scrollbar-thumb:hover {
        background: var(--secondary);
    }

    .premium-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        min-width: 600px; /* Ensures reasonable layout on small screens */
    }

    .premium-table th {
        font-size: 0.7rem;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        font-weight: 700;
        color: var(--text-muted);
        background-color: #f8fafc;
        padding: 0.875rem 1rem;
        border-bottom: 2px solid #e8edf2;
        white-space: nowrap;
        text-align: left;
    }

    .premium-table td {
        padding: 0.9rem 1rem;
        border-bottom: 1px solid #f1f5f9;
        font-size: 0.9rem;
        vertical-align: middle;
        white-space: nowrap;
        transition: background 0.1s;
    }

    /* Special alignments */
    .text-right {
        text-align: right !important;
    }

    .text-center {
        text-align: center !important;
    }

    /* Last column often needs right alignment for buttons */
    .premium-table td:last-child {
        text-align: right;
    }

    .premium-table tr:last-child td {
        border-bottom: none;
    }

    .premium-table tbody tr:hover td {
        background-color: #f8fafc;
    }
</style>
