@extends('layouts.app')

@section('title', 'Edit Funder')
@section('header', 'Edit Funder')

@section('header-actions')
<a href="{{ route('funders.index') }}" class="btn btn-outline-secondary">
    <i class="bx bx-arrow-back me-1 text-success"></i> Back to List
</a>
@endsection

@section('content')
<div class="card shadow-sm border-0 mb-5">
    <div class="card-header bg-white border-bottom">
        <h5 class="mb-0 text-dark">
            <i class="fas fa-handshake me-2"></i> Funder Details
        </h5>
    </div>
    <div class="card-body p-4">
        <form action="{{ route('funders.update', $funder) }}" method="POST" id="funderForm">
            @csrf
            @method('PUT')
            <input type="hidden" name="_token" value="{{ csrf_token() }}">

            <!-- Section 1: Basic Information -->
            <div class="mb-5">
                <h6 class="fw-bold text-success mb-4 border-bottom pb-2">
                    <i class="fas fa-info-circle me-2"></i> Basic Information
                </h6>
                
                <div class="row g-4">
                    <!-- Funder Name -->
                    <div class="col-md-6">
                        <div class="form-group position-relative">
                            <label for="name" class="form-label fw-semibold">
                                <i class="bx bx-building me-1 text-success"></i> Funder Name <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   class="form-control form-control-lg border-success @error('name') is-invalid @enderror" 
                                   id="name" 
                                   name="name" 
                                   value="{{ old('name', $funder->name) }}" 
                                   placeholder="Enter funder name"
                                   required>
                            <small class="text-muted mt-1 d-block">Official name of the funding organization</small>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Contact Person -->
                    <div class="col-md-6">
                        <div class="form-group position-relative">
                            <label for="contact_person" class="form-label fw-semibold">
                                <i class="bx bx-user me-1 text-success"></i> Contact Person
                            </label>
                            <input type="text" 
                                   class="form-control form-control-lg border-success @error('contact_person') is-invalid @enderror" 
                                   id="contact_person" 
                                   name="contact_person" 
                                   value="{{ old('contact_person', $funder->contact_person) }}" 
                                   placeholder="Contact person name">
                            <small class="text-muted mt-1 d-block">Primary contact person at the organization</small>
                            @error('contact_person')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section 2: Contact Information -->
            <div class="mb-5">
                <h6 class="fw-bold text-success mb-4 border-bottom pb-2">
                    <i class="fas fa-address-book me-2"></i> Contact Information
                </h6>
                
                <div class="row g-4">
                    <!-- Email Address -->
                    <div class="col-md-4">
                        <div class="form-group position-relative">
                            <label for="email" class="form-label fw-semibold">
                                <i class="bx bx-envelope me-1 text-success"></i> Email Address
                            </label>
                            <input type="email" 
                                   class="form-control form-control-lg border-success @error('email') is-invalid @enderror" 
                                   id="email" 
                                   name="email" 
                                   value="{{ old('email', $funder->email) }}" 
                                   placeholder="contact@funder.org">
                            <small class="text-muted mt-1 d-block">Primary email for communication</small>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Phone Number -->
                    <div class="col-md-4">
                        <div class="form-group position-relative">
                            <label for="phone" class="form-label fw-semibold">
                                <i class="bx bx-phone me-1 text-success"></i> Phone Number
                            </label>
                            <input type="text" 
                                   class="form-control form-control-lg border-success @error('phone') is-invalid @enderror" 
                                   id="phone" 
                                   name="phone" 
                                   value="{{ old('phone', $funder->phone) }}" 
                                   placeholder="+1 (555) 123-4567">
                            <small class="text-muted mt-1 d-block">Primary phone number</small>
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Website -->
                    <div class="col-md-4">
                        <div class="form-group position-relative">
                            <label for="website" class="form-label fw-semibold">
                                <i class="bx bx-globe me-1 text-success"></i> Website
                            </label>
                            <input type="url" 
                                   class="form-control form-control-lg border-success @error('website') is-invalid @enderror" 
                                   id="website" 
                                   name="website" 
                                   value="{{ old('website', $funder->website) }}" 
                                   placeholder="https://www.funder.org">
                            <small class="text-muted mt-1 d-block">Official website URL</small>
                            @error('website')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section 3: Additional Information -->
            <div class="mb-5">
                <h6 class="fw-bold text-success mb-4 border-bottom pb-2">
                    <i class="fas fa-info me-2"></i> Additional Information
                </h6>
                
                <div class="row g-4">
                    <!-- Description -->
                    <div class="col-md-8">
                        <div class="form-group position-relative">
                            <label for="description" class="form-label fw-semibold">
                                <i class="bx bx-detail me-1 text-success"></i> Description
                            </label>
                            <textarea 
                                class="form-control border-success @error('description') is-invalid @enderror" 
                                id="description" 
                                name="description" 
                                rows="4" 
                                placeholder="Enter detailed description about the funder">{{ old('description', $funder->description) }}</textarea>
                            <small class="text-muted mt-1 d-block">Additional information about the funder organization</small>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Status -->
                    <div class="col-md-4">
                        <div class="form-group position-relative">
                            <label for="is_active" class="form-label fw-semibold">
                                <i class="bx bx-check-circle me-1 text-success"></i> Status
                            </label>
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', $funder->is_active) ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold" for="is_active">Active</label>
                            </div>
                            <small class="text-muted d-block">Inactive funders will not be available for selection</small>
                        </div>
                    </div>
                </div>

                <!-- Address -->
                <div class="row g-4 mt-2">
                    <div class="col-md-12">
                        <div class="form-group position-relative">
                            <label for="address" class="form-label fw-semibold">
                                <i class="bx bx-map me-1 text-success"></i> Address
                            </label>
                            <textarea 
                                class="form-control border-success @error('address') is-invalid @enderror" 
                                id="address" 
                                name="address" 
                                rows="3" 
                                placeholder="Enter complete address">{{ old('address', $funder->address) }}</textarea>
                            <small class="text-muted mt-1 d-block">Physical address of the funder organization</small>
                            @error('address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="d-flex justify-content-between align-items-center border-top pt-4 mt-4">
                <a href="{{ route('funders.index') }}" class="btn btn-outline-secondary px-4">
                    <i class="bx bx-arrow-back me-1"></i> Cancel
                </a>
                <button type="submit" class="btn btn-warning btn-lg px-5 shadow-sm">
                    <i class="bx bx-save me-2"></i> Update Funder
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
