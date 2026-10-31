@php
    $inv = $invoice ?? null;
    $lines = old('items', $inv?->items?->map(fn ($i) => ['description' => $i->description, 'quantity' => (float) $i->quantity, 'unit_price' => (float) $i->unit_price])->all() ?: [['description' => '', 'quantity' => 1, 'unit_price' => '']]);
    $selectedCustomer = old('customer_id', $inv?->customer_id ?? request('customer'));
@endphp
<div class="row g-2 mb-3">
    <div class="col-12 col-md-6">
        <label for="invCustomer" class="form-label small fw-semibold">{{ __('ops.customer') }} *</label>
        <select id="invCustomer" name="customer_id" class="form-select select2 w-100" required>
            <option value="">{{ __('ops.select_customer') }}</option>
            @foreach($customers as $c)
                <option value="{{ $c->id }}" @selected((string) $selectedCustomer === (string) $c->id)>{{ $c->first_name }} {{ $c->last_name }} ({{ format_phone_tz($c->phone) }})</option>
            @endforeach
        </select>
    </div>
    <div class="col-12 col-md-6">
        <label for="invProperty" class="form-label small fw-semibold">{{ __('ops.linked_property') }}</label>
        <select id="invProperty" name="property_id" class="form-select w-100">
            <option value="">{{ __('ops.none') }}</option>
            @foreach($properties as $pr)
                <option value="{{ $pr->id }}" @selected((string) old('property_id', $inv?->property_id) === (string) $pr->id)>{{ $pr->title }}</option>
            @endforeach
        </select>
    </div>
</div>
<div class="row g-2 mb-3">
    <div class="col-6">
        <label for="invIssueDate" class="form-label small fw-semibold">{{ __('ops.issue_date') }} *</label>
        <input type="date" id="invIssueDate" name="issue_date" class="form-control" value="{{ old('issue_date', $inv?->issue_date?->format('Y-m-d') ?? date('Y-m-d')) }}" required>
    </div>
    <div class="col-6">
        <label for="invDueDate" class="form-label small fw-semibold">{{ __('ops.due_date') }} *</label>
        <input type="date" id="invDueDate" name="due_date" class="form-control" value="{{ old('due_date', $inv?->due_date?->format('Y-m-d') ?? date('Y-m-d', strtotime('+14 days'))) }}" required>
    </div>
</div>

<div class="d-flex justify-content-between align-items-center mb-2">
    <h6 class="brand-font mb-0">{{ __('ops.invoice_lines') }}</h6>
    <button type="button" class="btn btn-sm btn-light border inv-add-line"><i class="bi bi-plus-lg me-1"></i>{{ __('ops.add_line') }}</button>
</div>
<div class="inv-lines d-flex flex-column gap-2 mb-3">
    @foreach($lines as $i => $line)
        <div class="row g-2 align-items-end inv-line">
            <div class="col-12 col-md-6">
                <label class="form-label small fw-semibold {{ $loop->first ? '' : 'd-md-none' }}">{{ __('app.description') }} *</label>
                <input type="text" name="items[{{ $i }}][description]" class="form-control" value="{{ $line['description'] }}" placeholder="{{ __('ops.item_description_placeholder') }}" required>
            </div>
            <div class="col-3 col-md-2">
                <label class="form-label small fw-semibold {{ $loop->first ? '' : 'd-md-none' }}">{{ __('ops.quantity') }}</label>
                <input type="number" name="items[{{ $i }}][quantity]" class="form-control inv-qty" value="{{ $line['quantity'] }}" min="0.01" step="any" required>
            </div>
            <div class="col-7 col-md-3">
                <label class="form-label small fw-semibold {{ $loop->first ? '' : 'd-md-none' }}">{{ __('ops.unit_price') }} (TSh)</label>
                <input type="number" name="items[{{ $i }}][unit_price]" class="form-control inv-price" value="{{ $line['unit_price'] }}" min="0" step="any" required>
            </div>
            <div class="col-2 col-md-1 text-end">
                <button type="button" class="btn btn-light border btn-icon inv-remove-line" title="{{ __('app.delete') }}" aria-label="{{ __('app.delete') }}"><i class="bi bi-x-lg text-danger"></i></button>
            </div>
        </div>
    @endforeach
</div>

<div class="row g-2 mb-3">
    <div class="col-6 col-md-3">
        <label for="invTax" class="form-label small fw-semibold">{{ __('ops.tax_rate') }} (%)</label>
        <input type="number" id="invTax" name="tax_rate" class="form-control inv-tax" value="{{ old('tax_rate', (float) ($inv?->tax_rate ?? 0)) }}" min="0" max="100" step="any">
    </div>
    <div class="col-6 col-md-3">
        <label for="invDiscount" class="form-label small fw-semibold">{{ __('ops.discount') }} (TSh)</label>
        <input type="number" id="invDiscount" name="discount_amount" class="form-control inv-discount" value="{{ old('discount_amount', (float) ($inv?->discount_amount ?? 0)) }}" min="0" step="any">
    </div>
    <div class="col-12 col-md-6">
        <div class="bg-light rounded-3 p-2 small h-100 d-flex flex-column justify-content-center">
            <div class="d-flex justify-content-between"><span class="text-muted">{{ __('ops.subtotal') }}</span><span class="inv-subtotal">0</span></div>
            <div class="d-flex justify-content-between"><span class="text-muted">{{ __('ops.tax') }}</span><span class="inv-taxtotal">0</span></div>
            <div class="d-flex justify-content-between fw-bold border-top mt-1 pt-1"><span>{{ __('app.total') }}</span><span class="inv-grandtotal">0</span></div>
        </div>
    </div>
</div>
<div>
    <label for="invNotes" class="form-label small fw-semibold">{{ __('ops.invoice_notes') }}</label>
    <textarea id="invNotes" name="notes" rows="2" class="form-control" maxlength="2000" placeholder="{{ __('ops.invoice_notes_placeholder') }}">{{ old('notes', $inv?->notes) }}</textarea>
</div>
