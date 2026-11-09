@forelse ($localidades as $n => $localidad)
    <tr data-nombre="{{ strtolower($localidad->nombre) }}">
        <td class="text-center">{{ $localidades->firstItem() + $n }}</td>
        <td>{{ $localidad->nombre }}</td>
        <td class="text-center">
            <span class="color-badge d-inline-block rounded-circle"
                style="background-color: {{ $localidad->color }}; width: 32px; height: 32px;" data-bs-toggle="tooltip"
                title="{{ $localidad->color }}">
            </span>
        </td>
        <td class="text-center">
            <div class="btn-group" role="group">

                @if (Auth::guard('web')->user()->can('localidades.ver'))
                    <a href="{{ route('admin.localidades.ver', $localidad->id) }}" title="Ver Localidad"
                        class="btn btn-primary btn-sm">
                        <i class="ri-eye-line"></i>
                    </a>
                @endif
                @if (Auth::guard('web')->user()->can('localidades.editar'))
                    <button type="button" title="Editar Datos Localidad" class="btn btn-warning btn-sm editBtn"
                        data-bs-obj='@json($localidad)' data-bs-toggle="modal"
                        data-bs-target="#modalModificar">
                        <i class="ri-edit-line"></i>
                    </button>
                @endif
                @if (Auth::guard('web')->user()->can('localidades.eliminar'))
                    <button type="button" title="Eliminar Localidad" class="btn btn-danger btn-sm deleteBtn"
                        data-bs-obj='@json($localidad)' data-bs-toggle="modal"
                        data-bs-target="#modalEliminar">
                        <i class="ri-delete-bin-line"></i>
                    </button>
                @endif
            </div>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="4" class="text-center py-4">
            <div class="text-muted">
                <i class="ri-inbox-line display-4"></i>
                <p class="mt-2">No se tienen registros de Localidades</p>
            </div>
        </td>
    </tr>
@endforelse
