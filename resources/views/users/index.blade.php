@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-12">
        <!-- Header Card -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="card-title mb-0 fw-bold">
                            <i class="fas fa-users me-2"></i>Users Management
                        </h4>
                        <p class="text-muted mb-0">Manage system users and permissions</p>
                    </div>
                    <button class="btn btn-primary" id="toggleForm">
                        <i class="fas fa-plus me-2"></i>Create New User
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create User Form Card -->
<div class="row mb-4" id="createUserForm" style="display: none;">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-light">
                <h5 class="mb-0"><i class="fas fa-user-plus me-2"></i>Add New User</h5>
            </div>
            <div class="card-body">
                <form id="userForm">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="name" class="form-label">Full Name *</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                            <div class="invalid-feedback" id="name-error"></div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="email" class="form-label">Email Address *</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                            <div class="invalid-feedback" id="email-error"></div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="role" class="form-label">Role *</label>
                            <select class="form-select" id="role" name="role" required>
                                <option value="">Select Role</option>
                                <option value="user">User</option>
                                <option value="manager">Manager</option>
                                <option value="admin">Admin</option>
                            </select>
                            <div class="invalid-feedback" id="role-error"></div>
                        </div>
                    </div>
                    <div class="d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-secondary" id="cancelForm">
                            Cancel
                        </button>
                        <button type="submit" class="btn btn-primary" id="submitBtn">
                            <span id="submitText">Create User</span>
                            <span id="loadingSpinner" style="display: none;">
                                <i class="fas fa-spinner fa-spin"></i> Processing...
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Users Table Card -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover" id="usersTable" style="width:100%">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>User</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Toggle create form
    $('#toggleForm').click(function() {
        $('#createUserForm').slideToggle('fast', function() {
            if ($(this).is(':visible')) {
                $('#toggleForm').html('<i class="fas fa-times me-2"></i>Close Form');
            } else {
                $('#toggleForm').html('<i class="fas fa-plus me-2"></i>Create New User');
                resetForm();
            }
        });
    });
    
    $('#cancelForm').click(function() {
        $('#createUserForm').slideUp();
        $('#toggleForm').html('<i class="fas fa-plus me-2"></i>Create New User');
        resetForm();
    });
    
    // Initialize DataTable
    const table = $('#usersTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('users.data') }}",
        responsive: true,
        order: [[0, 'desc']],
        columns: [
            { 
                data: 'id',
                className: 'fw-semibold'
            },
            { 
                data: 'name',
                render: function(data, type, row) {
                    return `<div class="d-flex align-items-center">
                                <div class="avatar avatar-sm me-3">
                                    <div class="avatar-initial bg-primary rounded-circle text-white">
                                        ${data.charAt(0).toUpperCase()}
                                    </div>
                                </div>
                                <div>
                                    <h6 class="mb-0">${data}</h6>
                                </div>
                            </div>`;
                }
            },
            { 
                data: 'email',
                render: function(data) {
                    return `<a href="mailto:${data}" class="text-decoration-none">${data}</a>`;
                }
            },
            { 
                data: 'role',
                render: function(data) {
                    const badgeClass = {
                        'admin': 'bg-danger',
                        'manager': 'bg-primary',
                        'user': 'bg-secondary'
                    }[data] || 'bg-secondary';
                    
                    return `<span class="badge ${badgeClass}">${data.charAt(0).toUpperCase() + data.slice(1)}</span>`;
                }
            },
            { 
                data: 'active',
                render: function(data) {
                    if (data) {
                        return `<span class="badge bg-success">
                                    <i class="fas fa-check-circle me-1"></i>Active
                                </span>`;
                    } else {
                        return `<span class="badge bg-warning text-dark">
                                    <i class="fas fa-clock me-1"></i>Pending
                                </span>`;
                    }
                }
            },
            { 
                data: 'created_at',
                render: function(data) {
                    return new Date(data).toLocaleDateString('en-US', {
                        year: 'numeric',
                        month: 'short',
                        day: 'numeric'
                    });
                }
            },
            { 
                data: 'id',
                orderable: false,
                searchable: false,
                className: 'text-end',
                render: function(data, type, row) {
                    let buttons = '<div class="btn-group" role="group">';
                    
                    if (!row.active) {
                        buttons += `<button class="btn btn-sm btn-outline-warning btn-resend" 
                                      data-id="${data}" 
                                      data-name="${row.name}"
                                      title="Resend Invitation">
                                    <i class="fas fa-paper-plane"></i>
                                </button>`;
                    }
                    
                    buttons += `<button class="btn btn-sm btn-outline-primary btn-edit"
                                  data-id="${data}"
                                  data-name="${row.name}"
                                  data-email="${row.email}"
                                  data-role="${row.role}"
                                  title="Edit User">
                                <i class="fas fa-edit"></i>
                            </button>
                            
                            <button class="btn btn-sm btn-outline-danger btn-delete"
                                    data-id="${data}"
                                    data-name="${row.name}"
                                    title="Delete User">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>`;
                    
                    return buttons;
                }
            }
        ],
        language: {
            search: "_INPUT_",
            searchPlaceholder: "Search users...",
            lengthMenu: "_MENU_ records per page",
            zeroRecords: "No users found",
            info: "Showing _START_ to _END_ of _TOTAL_ users",
            infoEmpty: "Showing 0 to 0 of 0 users",
            infoFiltered: "(filtered from _MAX_ total users)"
        }
    });
    
    // Create user form submission
    $('#userForm').submit(function(e) {
        e.preventDefault();
        
        const submitBtn = $('#submitBtn');
        const submitText = $('#submitText');
        const loadingSpinner = $('#loadingSpinner');
        
        // Show loading state
        submitBtn.prop('disabled', true);
        submitText.hide();
        loadingSpinner.show();
        
        // Clear previous errors
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').text('');
        
        $.ajax({
            url: "{{ route('users.store') }}",
            method: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
                Toast.fire({
                    icon: 'success',
                    title: response.message
                });
                
                resetForm();
                table.ajax.reload();
                
                // Hide form after success
                $('#createUserForm').slideUp();
                $('#toggleForm').html('<i class="fas fa-plus me-2"></i>Create New User');
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;
                    $.each(errors, function(key, value) {
                        $(`#${key}`).addClass('is-invalid');
                        $(`#${key}-error`).text(value[0]);
                    });
                    
                    Toast.fire({
                        icon: 'error',
                        title: 'Please fix the form errors'
                    });
                } else {
                    Toast.fire({
                        icon: 'error',
                        title: xhr.responseJSON?.message || 'An error occurred'
                    });
                }
            },
            complete: function() {
                // Reset button state
                submitBtn.prop('disabled', false);
                submitText.show();
                loadingSpinner.hide();
            }
        });
    });
    
    // Resend invitation
    $(document).on('click', '.btn-resend', function() {
        const userId = $(this).data('id');
        const userName = $(this).data('name');
        const button = $(this);
        
        Swal.fire({
            title: 'Resend Invitation',
            html: `<p>Are you sure you want to resend invitation to <strong>${userName}</strong>?</p>`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, resend it!',
            cancelButtonText: 'Cancel',
            showLoaderOnConfirm: true,
            preConfirm: () => {
                return $.ajax({
                    url: `/users/${userId}/resend`,
                    method: 'POST',
                    dataType: 'json'
                }).catch(error => {
                    Swal.showValidationMessage(
                        `Request failed: ${error.responseJSON?.message || error.statusText}`
                    );
                });
            }
        }).then((result) => {
            if (result.isConfirmed) {
                Toast.fire({
                    icon: 'success',
                    title: 'Invitation resent successfully!'
                });
                
                // Update button state
                button.prop('disabled', true)
                      .removeClass('btn-outline-warning')
                      .addClass('btn-secondary')
                      .html('<i class="fas fa-check"></i>');
                
                // Reload table data
                table.ajax.reload(null, false);
            }
        });
    });
    
    // Delete user
    $(document).on('click', '.btn-delete', function() {
        const userId = $(this).data('id');
        const userName = $(this).data('name');
        
        Swal.fire({
            title: 'Delete User',
            html: `<p>Are you sure you want to delete <strong>${userName}</strong>?</p>
                  <p class="text-danger"><small>This action cannot be undone.</small></p>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/users/${userId}`,
                    method: 'DELETE',
                    dataType: 'json',
                    success: function(response) {
                        Toast.fire({
                            icon: 'success',
                            title: response.message
                        });
                        table.ajax.reload();
                    },
                    error: function(xhr) {
                        Toast.fire({
                            icon: 'error',
                            title: xhr.responseJSON?.message || 'Failed to delete user'
                        });
                    }
                });
            }
        });
    });
    
    // Edit user (placeholder for now)
    $(document).on('click', '.btn-edit', function() {
        const userId = $(this).data('id');
        window.location.href = `/users/${userId}/edit`;
    });
    
    // Reset form
    function resetForm() {
        $('#userForm')[0].reset();
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').text('');
    }
});
</script>
@endpush