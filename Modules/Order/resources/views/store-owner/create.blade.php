@extends('layouts.store-owner')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">إنشاء طلب جديد</h5>
                    <a href="{{ route('store-owner.order.index') }}" class="btn btn-secondary btn-sm">العودة إلى القائمة</a>
                </div>

                <div class="card-body">
                    @if (session('error'))
                        <div class="alert alert-danger" role="alert">
                            {{ session('error') }}
                        </div>
                    @endif

                    <form action="{{ route('store-owner.order.store') }}" method="POST" id="orderForm">
                        @csrf
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <input type="hidden" name="store_id" value="{{ auth('store-owner')->user()->store_id }}">
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
                                            <tr id="noItemsRow">
                                                <td colspan="5" class="text-center">لم يتم إضافة أي عناصر بعد.</td>
                                            </tr>
                                            <!-- Default item row -->
                                            <tr class="item-row">
                                                <td>
                                                    <select class="form-select product-select" name="items[0][product_id]" required>
                                                        <option value="">اختر المنتج</option>
                                                        @foreach($products as $product)
                                                            <option value="{{ $product->id }}" 
                                                                data-price="{{ $product->price }}" 
                                                                data-stock="{{ $product->stock }}">
                                                                {{ $product->name }} (المخزون: {{ $product->stock }})
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    <input type="hidden" class="item-price-input" name="items[0][price]" value="0">
                                                </td>
                                                <td class="item-price">$0.00</td>
                                                <td>
                                                    <input type="number" class="form-control quantity-input" name="items[0][quantity]" min="1" value="1" required>
                                                </td>
                                                <td class="item-subtotal">$0.00</td>
                                                <td>
                                                    <button type="button" class="btn btn-danger btn-sm remove-item-btn">حذف</button>
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
                                    <label for="shipping_address" class="form-label">عنوان الشحن <span class="text-danger">*</span></label>
                                    <textarea class="form-control @error('shipping_address') is-invalid @enderror" id="shipping_address" name="shipping_address" rows="3" required>{{ old('shipping_address') }}</textarea>
                                    @error('shipping_address')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="billing_address" class="form-label">عنوان الفوترة <span class="text-danger">*</span></label>
                                    <textarea class="form-control @error('billing_address') is-invalid @enderror" id="billing_address" name="billing_address" rows="3" required>{{ old('billing_address') }}</textarea>
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
                                        <option value="cash" {{ old('payment_method') == 'cash' ? 'selected' : '' }}>نقداً عند الاستلام</option>
                                        <option value="credit_card" {{ old('payment_method') == 'credit_card' ? 'selected' : '' }}>بطاقة ائتمان</option>
                                        <option value="bank_transfer" {{ old('payment_method') == 'bank_transfer' ? 'selected' : '' }}>تحويل بنكي</option>
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

                        <div class="text-center">
                            <button type="submit" class="btn btn-primary">إنشاء الطلب</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const itemsContainer = document.getElementById('itemsContainer');
        const noItemsRow = document.getElementById('noItemsRow');
        const addItemBtn = document.getElementById('addItemBtn');
        const totalAmountEl = document.getElementById('totalAmount');
        let itemCounter = 1; // Start from 1 because we already have item[0]
        
        // Add a new item row
        function addItemRow() {
            // Hide the "no items" row
            noItemsRow.style.display = 'none';
            
            // Clone the first item row
            const templateRow = itemsContainer.querySelector('.item-row');
            const newRow = templateRow.cloneNode(true);
            
            // Update the input names with the new index
            const productSelect = newRow.querySelector('.product-select');
            const priceInput = newRow.querySelector('.item-price-input');
            const quantityInput = newRow.querySelector('.quantity-input');
            
            productSelect.name = `items[${itemCounter}][product_id]`;
            priceInput.name = `items[${itemCounter}][price]`;
            quantityInput.name = `items[${itemCounter}][quantity]`;
            
            // Reset the values
            productSelect.value = '';
            priceInput.value = '0';
            quantityInput.value = '1';
            newRow.querySelector('.item-price').textContent = '$0.00';
            newRow.querySelector('.item-subtotal').textContent = '$0.00';
            
            // Add the new row to the container
            itemsContainer.appendChild(newRow);
            
            // Set up event listeners for the new row
            setupRowEventListeners(newRow);
            
            // Increment the counter
            itemCounter++;
        }
        
        // Set up event listeners for a row
        function setupRowEventListeners(row) {
            const productSelect = row.querySelector('.product-select');
            const quantityInput = row.querySelector('.quantity-input');
            const removeBtn = row.querySelector('.remove-item-btn');
            
            productSelect.addEventListener('change', function() {
                updateRowPrices(row);
            });
            
            quantityInput.addEventListener('change', function() {
                // Ensure quantity is at least 1
                if (parseInt(this.value) < 1) {
                    this.value = 1;
                }
                
                // Check against available stock
                const option = productSelect.options[productSelect.selectedIndex];
                if (option && option.value) {
                    const stock = parseInt(option.dataset.stock);
                    if (parseInt(this.value) > stock) {
                        this.value = stock;
                        alert('لا يمكن أن تتجاوز الكمية المخزون المتاح.');
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
            const priceInput = row.querySelector('.item-price-input');
            const priceCell = row.querySelector('.item-price');
            const subtotalCell = row.querySelector('.item-subtotal');
            
            const option = productSelect.options[productSelect.selectedIndex];
            
            if (option && option.value) {
                const price = parseFloat(option.dataset.price);
                const quantity = parseInt(quantityInput.value);
                
                // Update the hidden price input
                priceInput.value = price;
                
                priceCell.textContent = '$' + price.toFixed(2);
                subtotalCell.textContent = '$' + (price * quantity).toFixed(2);
            } else {
                priceInput.value = 0;
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
                alert('الرجاء إضافة عنصر واحد على الأقل إلى الطلب.');
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
                alert('الرجاء اختيار منتج لجميع عناصر الطلب.');
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
