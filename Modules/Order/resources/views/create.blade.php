@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">إنشاء طلب جديد</h5>
                    <a href="{{ route('order.index') }}" class="btn btn-secondary btn-sm">العودة إلى القائمة</a>
                </div>

                <div class="card-body">
                    @if (session('error'))
                        <div class="alert alert-danger" role="alert">
                            {{ session('error') }}
                        </div>
                    @endif

                    <form action="{{ route('order.store') }}" method="POST" id="orderForm">
                        @csrf
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="store_id" class="form-label">المتجر <span class="text-danger">*</span></label>
                                    <select class="form-select @error('store_id') is-invalid @enderror" id="store_id" name="store_id" required>
                                        <option value="">اختر المتجر</option>
                                        @foreach($stores as $store)
                                            <option value="{{ $store->id }}" {{ old('store_id') == $store->id ? 'selected' : '' }}>
                                                {{ $store->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('store_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="user_id" class="form-label">العميل <span class="text-danger">*</span></label>
                                    <select class="form-select @error('user_id') is-invalid @enderror" id="user_id" name="user_id" required>
                                        <option value="">اختر العميل</option>
                                        @foreach(App\Models\User::all() as $user)
                                            <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>
                                                {{ $user->name }} ({{ $user->email }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('user_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="card mb-4">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h6 class="mb-0">عناصر الطلب</h6>
                                <button type="button" class="btn btn-sm btn-success" id="addItemBtn">إضافة عنصر</button>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered" id="itemsTable">
                                        <thead>
                                            <tr>
                                                <th>المنتج</th>
                                                <th>السعر</th>
                                                <th>الكمية</th>
                                                <th>المجموع الفرعي</th>
                                                <th>الإجراء</th>
                                            </tr>
                                        </thead>
                                        <tbody id="itemsContainer">
                                               <!-- No items row will be hidden when items are added -->
                                            <tr class="no-items-row" style="display: none;">
                                                <td colspan="5" class="text-center">لم يتم إضافة أي عناصر بعد.</td>
                                            </tr>
                                            <!-- Default item row -->
                                            <tr class="item-row">
                                                <td>
                                                    <select class="form-select product-select" name="items[0][product_id]" required>
                                                        <option value="">Select Product</option>
                                                        @foreach($products as $product)
                                                            <option value="{{ $product->id }}" data-price="{{ $product->price }}" data-stock="{{ $product->stock }}">
                                                                {{ $product->name }} ({{ $product->sku }}) - ${{ number_format($product->price, 2) }} - Stock: {{ $product->stock }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </td>
                                                <td class="item-price">$0.00</td>
                                                <td>
                                                    <input type="number" class="form-control quantity-input" name="items[0][quantity]" min="1" value="1" required>
                                                </td>
                                                <td class="item-subtotal">$0.00</td>
                                                <td>
                                                    <button type="button" class="btn btn-danger btn-sm remove-item-btn">Remove</button>
                                                </td>
                                            </tr>
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <th colspan="3" class="text-end">المجموع:</th>
                                                <th id="totalAmount">$0.00</th>
                                                <th></th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="shipping_address" class="form-label">عنوان الشحن</label>
                                    <textarea class="form-control @error('shipping_address') is-invalid @enderror" id="shipping_address" name="shipping_address" rows="3">{{ old('shipping_address') }}</textarea>
                                    @error('shipping_address')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="billing_address" class="form-label">عنوان الفواتير</label>
                                    <textarea class="form-control @error('billing_address') is-invalid @enderror" id="billing_address" name="billing_address" rows="3">{{ old('billing_address') }}</textarea>
                                    @error('billing_address')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="payment_method" class="form-label">طريقة الدفع</label>
                                    <select class="form-select @error('payment_method') is-invalid @enderror" id="payment_method" name="payment_method">
                                        <option value="">اختر طريقة الدفع</option>
                                        <option value="credit_card" {{ old('payment_method') == 'credit_card' ? 'selected' : '' }}>بطاقة ائتمان</option>
                                        <option value="paypal" {{ old('payment_method') == 'paypal' ? 'selected' : '' }}>باي بال</option>
                                        <option value="bank_transfer" {{ old('payment_method') == 'bank_transfer' ? 'selected' : '' }}>تحويل بنكي</option>
                                        <option value="cash_on_delivery" {{ old('payment_method') == 'cash_on_delivery' ? 'selected' : '' }}>الدفع عند الاستلام</option>
                                    </select>
                                    @error('payment_method')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="notes" class="form-label">ملاحظات</label>
                                    <textarea class="form-control @error('notes') is-invalid @enderror" id="notes" name="notes" rows="3">{{ old('notes') }}</textarea>
                                    @error('notes')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <button type="reset" class="btn btn-secondary">إعادة تعيين</button>
                            <button type="submit" class="btn btn-primary" id="submitBtn">إنشاء الطلب</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Item Template (hidden) -->
<template id="itemTemplate">
    <tr class="item-row">
        <td>
            <select class="form-select product-select" name="items[{index}][product_id]" required>
                <option value="">Select Product</option>
                @foreach($products as $product)
                    <option value="{{ $product->id }}" data-price="{{ $product->price }}" data-stock="{{ $product->stock }}">
                        {{ $product->name }} ({{ $product->sku }}) - ${{ number_format($product->price, 2) }} - Stock: {{ $product->stock }}
                    </option>
                @endforeach
            </select>
        </td>
        <td class="item-price">$0.00</td>
        <td>
            <input type="number" class="form-control quantity-input" name="items[{index}][quantity]" min="1" value="1" required>
        </td>
        <td class="item-subtotal">$0.00</td>
        <td>
            <button type="button" class="btn btn-danger btn-sm remove-item-btn">Remove</button>
        </td>
    </tr>
</template>

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        let itemIndex = 1; // Start at 1 since we already have item[0] in the HTML
        const itemsContainer = document.getElementById('itemsContainer');
        const itemTemplate = document.getElementById('itemTemplate');
        const addItemBtn = document.getElementById('addItemBtn');
        const totalAmountEl = document.getElementById('totalAmount');
        const noItemsRow = document.querySelector('.no-items-row');
        
        // Function to add a new item row
        function addItemRow() {
            if (noItemsRow) {
                noItemsRow.style.display = 'none';
            }
            
            const template = itemTemplate.innerHTML.replace(/{index}/g, itemIndex);
            const tempDiv = document.createElement('div');
            tempDiv.innerHTML = template;
            const newRow = tempDiv.firstElementChild;
            
            itemsContainer.appendChild(newRow);
            
            // Setup event listeners for the new row
            setupRowEventListeners(newRow);
            
            itemIndex++;
            updateTotalAmount();
        }
        
        // Setup event listeners for a row
        function setupRowEventListeners(row) {
            const productSelect = row.querySelector('.product-select');
            const quantityInput = row.querySelector('.quantity-input');
            const removeBtn = row.querySelector('.remove-item-btn');
            
            productSelect.addEventListener('change', function() {
                updateRowPrices(row);
            });
            
            quantityInput.addEventListener('change', function() {
                if (parseInt(this.value) < 1) {
                    this.value = 1;
                }
                
                const option = productSelect.options[productSelect.selectedIndex];
                if (option && option.value) {
                    const stock = parseInt(option.dataset.stock);
                    if (parseInt(this.value) > stock) {
                        this.value = stock;
                        alert('Quantity cannot exceed available stock.');
                    }
                }
                
                updateRowPrices(row);
            });
            
            removeBtn.addEventListener('click', function() {
                row.remove();
                updateTotalAmount();
                
                if (itemsContainer.querySelectorAll('.item-row').length === 0) {
                    noItemsRow.style.display = '';
                }
            });
        }
        
        // Update prices for a row
        function updateRowPrices(row) {
            const productSelect = row.querySelector('.product-select');
            const quantityInput = row.querySelector('.quantity-input');
            const priceCell = row.querySelector('.item-price');
            const subtotalCell = row.querySelector('.item-subtotal');
            
            const option = productSelect.options[productSelect.selectedIndex];
            
            if (option && option.value) {
                const price = parseFloat(option.dataset.price);
                const quantity = parseInt(quantityInput.value);
                
                priceCell.textContent = '$' + price.toFixed(2);
                subtotalCell.textContent = '$' + (price * quantity).toFixed(2);
            } else {
                priceCell.textContent = '$0.00';
                subtotalCell.textContent = '$0.00';
            }
            
            updateTotalAmount();
        }
        
        // Update the total amount
        function updateTotalAmount() {
            let total = 0;
            const subtotalCells = itemsContainer.querySelectorAll('.item-subtotal');
            
            subtotalCells.forEach(function(cell) {
                const subtotal = parseFloat(cell.textContent.replace('$', ''));
                if (!isNaN(subtotal)) {
                    total += subtotal;
                }
            });
            
            totalAmountEl.textContent = '$' + total.toFixed(2);
        }
        
        // Add item button click handler
        addItemBtn.addEventListener('click', addItemRow);
        
        // Form submit validation
        document.getElementById('orderForm').addEventListener('submit', function(e) {
            const itemRows = itemsContainer.querySelectorAll('.item-row');
            
            if (itemRows.length === 0) {
                e.preventDefault();
                alert('Please add at least one item to the order.');
                return false;
            }
            
            // Check if all items have valid product selections
            let hasInvalidItem = false;
            itemRows.forEach(function(row) {
                const productSelect = row.querySelector('.product-select');
                if (!productSelect.value) {
                    hasInvalidItem = true;
                }
            });
            
            if (hasInvalidItem) {
                e.preventDefault();
                alert('Please select a product for all order items.');
                return false;
            }
            
            return true;
        });
        
        // Set up event listeners for the default item row
        const defaultItemRows = itemsContainer.querySelectorAll('.item-row');
        defaultItemRows.forEach(function(row) {
            setupRowEventListeners(row);
        });
        
        // Initialize the total amount
        updateTotalAmount();
    });
</script>
@endpush
