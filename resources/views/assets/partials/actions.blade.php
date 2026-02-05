<div class="action-buttons d-flex flex-wrap">
    <button class="btn btn-sm btn-info" onclick="showEditModal({{ $asset->id }})" 
            title="Editar">
        <i class="fas fa-edit"></i>
    </button>
    
    @if($asset->process_status === 'incompleto')
    <button class="btn btn-sm btn-warning" 
            onclick="showIncompleteReasonModal({{ $asset->id }})"
            title="Justificar Processo Incompleto">
        <i class="fas fa-question-circle"></i>
    </button>
    @endif
    
    @if($asset->asset_status === 'disponivel')
    <button class="btn btn-sm btn-primary" 
            onclick="showStatusChangeModal({{ $asset->id }}, 'assign', 'Atribuir Activo')"
            title="Atribuir">
        <i class="fas fa-user-check"></i>
    </button>
    @endif
    
    @if($asset->asset_status === 'atribuido')
    <button class="btn btn-sm btn-secondary" 
            onclick="performBulkAction('release', {asset_ids: [{{ $asset->id }}]})"
            title="Liberar">
        <i class="fas fa-user-times"></i>
    </button>
    @endif
    
    @if(!in_array($asset->asset_status, ['inoperacional', 'abatido']))
    <button class="btn btn-sm btn-danger" 
            onclick="showStatusChangeModal({{ $asset->id }}, 'inoperational', 'Marcar como Inoperacional')"
            title="Marcar como Inoperacional">
        <i class="fas fa-times-circle"></i>
    </button>
    
    <button class="btn btn-sm btn-dark" 
            onclick="showStatusChangeModal({{ $asset->id }}, 'writeOff', 'Abater Activo')"
            title="Abater Activo">
        <i class="fas fa-trash-alt"></i>
    </button>
    @endif
    
    @if(auth()->user()->isAdmin())
    <button class="btn btn-sm btn-outline-danger" 
            onclick="deleteAsset({{ $asset->id }})"
            title="Eliminar">
        <i class="fas fa-trash"></i>
    </button>
    @endif
</div>