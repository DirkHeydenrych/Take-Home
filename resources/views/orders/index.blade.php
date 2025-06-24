@extends('layouts.app')

@section('title', 'Order Viewer - E-commerce Order Management')

@section('content')
<div class="header">
    <h1>Order Viewer</h1>
    <p>Browse and manage e-commerce orders with real-time filtering and statistics</p>
</div> <!-- Error/Success Messages -->
<div id="message-container"></div>

<!-- Debug Info (remove in production) -->
<!--
<div style="background: #f0f0f0; padding: 10px; margin-bottom: 20px; border-radius: 4px; font-family: monospace; font-size: 12px;">
    <strong>Debug Info:</strong><br>
    API Base URL: <span id="debug-api-url">-</span><br>
    Orders API: <span id="debug-orders-url">-</span><br>
    Statistics API: <span id="debug-stats-url">-</span><br>
    Last API Call: <span id="debug-last-call">-</span><br>
    Last Response: <span id="debug-last-response">-</span>
</div>
-->

<!-- Filters Section -->
<div class="filters">
    <div class="filters-header" onclick="toggleFilters()" style="cursor: pointer; display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
        <h3 style="margin: 0;">Filter Orders</h3>
        <span id="filter-toggle-icon" class="filter-toggle">▼</span>
    </div>
    <div id="filter-content" class="filter-content">
        <form id="filter-form">
            <div class="filter-row">
                <div class="form-group">
                    <label for="status">Status</label>
                    <select id="status" name="status">
                        <option value="">All Statuses</option>
                        <option value="Pending">Pending</option>
                        <option value="Processing">Processing</option>
                        <option value="Shipped">Shipped</option>
                        <option value="Cancelled">Cancelled</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="start_date">Start Date</label>
                    <input type="date" id="start_date" name="start_date">
                </div>
                <div class="form-group">
                    <label for="end_date">End Date</label>
                    <input type="date" id="end_date" name="end_date">
                </div>
                <div class="form-group">
                    <label for="min_total">Min Total ($)</label>
                    <input type="number" id="min_total" name="min_total" step="0.01" min="0">
                </div>
                <div class="form-group">
                    <label for="max_total">Max Total ($)</label>
                    <input type="number" id="max_total" name="max_total" step="0.01" min="0">
                </div>
            </div>
            <div class="filter-actions">
                <button type="submit" class="btn btn-primary">Apply Filters</button>
                <button type="button" id="clear-filters" class="btn btn-secondary">Clear Filters</button> <button type="button" id="refresh-data" class="btn btn-secondary">Refresh</button>
            </div>
        </form>
    </div>
</div>

<!-- Live Statistics -->
<div class="stats">
    <h3 style="margin-bottom: 15px;">Live Statistics</h3>
    <div class="stats-grid" id="stats-container">
        <div class="stat-card">
            <div class="stat-number" id="total-orders">-</div>
            <div class="stat-label">Total Orders</div>
        </div>
        <div class="stat-card">
            <div class="stat-number" id="grand-total">-</div>
            <div class="stat-label">Grand Total</div>
        </div>
        <div class="stat-card">
            <div class="stat-number" id="average-total">-</div>
            <div class="stat-label">Average Order</div>
        </div>
        <div class="stat-card">
            <div class="stat-number" id="paid-orders">-</div>
            <div class="stat-label">Paid Orders</div>
        </div>
        <div class="stat-card">
            <div class="stat-number" id="pending-orders">-</div>
            <div class="stat-label">Pending</div>
        </div>
        <div class="stat-card">
            <div class="stat-number" id="processing-orders">-</div>
            <div class="stat-label">Processing</div>
        </div>
        <div class="stat-card">
            <div class="stat-number" id="shipped-orders">-</div>
            <div class="stat-label">Shipped</div>
        </div>
        <div class="stat-card">
            <div class="stat-number" id="cancelled-orders">-</div>
            <div class="stat-label">Cancelled</div>
        </div>
    </div>
</div>

<!-- Orders Table -->
<div class="orders-section">
    <div class="orders-header">
        <h3>Orders</h3>
    </div>
    <div class="table-container">
        <!-- Desktop Table View -->
        <table class="orders-table">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Customer</th>
                    <th>Email</th>
                    <th>Status</th>
                    <th>Total</th>
                    <th>Paid</th>
                    <th>Created Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="orders-tbody">
                <tr>
                    <td colspan="8" class="loading">Loading orders...</td>
                </tr>
            </tbody>
        </table>

        <!-- Mobile Card View -->
        <div class="mobile-orders" id="mobile-orders">
            <div class="loading" style="text-align: center; padding: 20px;">Loading orders...</div>
        </div>
    </div>
    <div class="pagination" id="pagination-container">
        <div class="pagination-info" id="pagination-info">-</div>
        <div class="pagination-controls" id="pagination-controls">
            <!-- Pagination buttons will be dynamically generated -->
        </div>
    </div>
</div>

<!-- Order Details Modal -->
<div class="modal" id="order-modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Order Details</h3>
        </div>
        <div class="modal-body" id="modal-body">
            <!-- Order details will be loaded here -->
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="orderViewer.closeModal()">Close</button>
            <button type="button" class="btn btn-success" id="mark-paid-btn" style="display: none;">Mark as Paid</button>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    class OrderViewer {
        constructor() {
            this.currentPage = 1;
            this.currentFilters = {};
            this.currentOrderId = null;
            this.debounceTimeout = null;

            this.init();
        }
        init() {
            this.setupEventListeners();
            this.updateDebugInfo();
            this.loadOrders();
            this.loadStatistics();
        }
        updateDebugInfo() {
            const debugApiUrl = document.getElementById('debug-api-url');
            const debugOrdersUrl = document.getElementById('debug-orders-url');
            const debugStatsUrl = document.getElementById('debug-stats-url');

            if (debugApiUrl) {
                debugApiUrl.textContent = window.API_BASE_URL || 'UNDEFINED';
            }
            if (debugOrdersUrl) {
                debugOrdersUrl.textContent = `${window.API_BASE_URL}/orders`;
            }
            if (debugStatsUrl) {
                debugStatsUrl.textContent = `${window.API_BASE_URL}/orders-statistics`;
            }
        }

        setupEventListeners() {
            // Filter form
            document.getElementById('filter-form').addEventListener('submit', (e) => {
                e.preventDefault();
                this.applyFilters();
            });

            // Clear filters
            document.getElementById('clear-filters').addEventListener('click', () => {
                this.clearFilters();
            });

            // Refresh data
            document.getElementById('refresh-data').addEventListener('click', () => {
                this.refreshData();
            });

            // Debounced filter inputs
            const filterInputs = ['status', 'start_date', 'end_date', 'min_total', 'max_total'];
            filterInputs.forEach(id => {
                document.getElementById(id).addEventListener('input', () => {
                    this.debounceFilter();
                });
            });

            // Mark as paid button
            document.getElementById('mark-paid-btn').addEventListener('click', () => {
                this.markOrderAsPaid();
            });

            // Close modal on background click
            document.getElementById('order-modal').addEventListener('click', (e) => {
                if (e.target.id === 'order-modal') {
                    this.closeModal();
                }
            });

            // ESC key to close modal
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') {
                    this.closeModal();
                }
            });
        }

        debounceFilter() {
            clearTimeout(this.debounceTimeout);
            this.debounceTimeout = setTimeout(() => {
                this.applyFilters();
            }, 500);
        }

        async applyFilters() {
            this.currentPage = 1;
            this.updateFiltersFromForm();
            await this.loadOrders();
            await this.loadStatistics();
        }

        updateFiltersFromForm() {
            const form = document.getElementById('filter-form');
            const formData = new FormData(form);
            this.currentFilters = {};

            for (const [key, value] of formData.entries()) {
                if (value.trim() !== '') {
                    this.currentFilters[key] = value;
                }
            }
        }

        clearFilters() {
            document.getElementById('filter-form').reset();
            this.currentFilters = {};
            this.currentPage = 1;
            this.loadOrders();
            this.loadStatistics();
        }

        refreshData() {
            this.loadOrders();
            this.loadStatistics();
        }
        async loadOrders() {
            try {
                const params = new URLSearchParams({
                    ...this.currentFilters,
                    page: this.currentPage,
                    per_page: 10
                });

                console.log('Loading orders with params:', params.toString());
                const url = `${window.API_BASE_URL}/orders?${params}`;
                console.log('API URL:', url); // Update debug info
                const debugLastCall = document.getElementById('debug-last-call');
                if (debugLastCall) {
                    debugLastCall.textContent = new Date().toLocaleTimeString() + ' - ' + url;
                }

                const response = await fetch(url);
                console.log('Response status:', response.status);

                if (!response.ok) {
                    const errorText = await response.text();
                    console.error('API Error Response:', errorText);
                    const debugLastResponse = document.getElementById('debug-last-response');
                    if (debugLastResponse) {
                        debugLastResponse.textContent = `ERROR ${response.status}: ${errorText.substring(0, 100)}`;
                    }
                    throw new Error(`HTTP ${response.status}: ${errorText}`);
                }

                const data = await response.json();
                console.log('Orders data received:', data);
                const debugLastResponse = document.getElementById('debug-last-response');
                if (debugLastResponse) {
                    debugLastResponse.textContent = `SUCCESS: ${data.data?.length || 0} orders, ${data.total || 0} total`;
                }

                this.renderOrders(data.data);
                this.renderPagination(data);
            } catch (error) {
                console.error('Error loading orders:', error);
                const debugLastResponse = document.getElementById('debug-last-response');
                if (debugLastResponse) {
                    debugLastResponse.textContent = `ERROR: ${error.message}`;
                }
                this.showError(`Network error occurred: ${error.message}`);
            }
        }
        async loadStatistics() {
            try {
                const params = new URLSearchParams(this.currentFilters);
                console.log('Loading statistics with params:', params.toString());
                const url = `${window.API_BASE_URL}/orders-statistics?${params}`;
                console.log('Statistics API URL:', url);

                const response = await fetch(url);
                console.log('Statistics response status:', response.status);

                if (!response.ok) {
                    const errorText = await response.text();
                    console.error('Statistics API Error:', errorText);
                    throw new Error(`HTTP ${response.status}: ${errorText}`);
                }

                const stats = await response.json();
                console.log('Statistics data received:', stats);

                this.renderStatistics(stats);
            } catch (error) {
                console.error('Error loading statistics:', error);
                // Don't show error for statistics as it's not critical
            }
        }
        renderOrders(orders) {
            const tbody = document.getElementById('orders-tbody');
            const mobileContainer = document.getElementById('mobile-orders');

            if (orders.length === 0) {
                tbody.innerHTML = '<tr><td colspan="8" class="loading">No orders found</td></tr>';
                mobileContainer.innerHTML = '<div class="loading" style="text-align: center; padding: 20px;">No orders found</div>';
                return;
            }

            // Desktop table view
            tbody.innerHTML = orders.map(order => {
                const statusClass = `status-${order.status.toLowerCase()}`;
                const paidClass = order.is_paid ? 'paid-yes' : 'paid-no';
                const paidText = order.is_paid ? 'Yes' : 'No';
                const createdDate = new Date(order.created_at).toLocaleDateString();

                return `
                    <tr onclick="orderViewer.showOrderDetails(${order.id})" data-order-id="${order.id}">
                        <td>#${order.id}</td>
                        <td>${this.escapeHtml(order.customer_name)}</td>
                        <td>${this.escapeHtml(order.customer_email)}</td>
                        <td><span class="status-badge ${statusClass}">${order.status}</span></td>
                        <td>$${parseFloat(order.total).toFixed(2)}</td>
                        <td><span class="paid-badge ${paidClass}">${paidText}</span></td>
                        <td>${createdDate}</td>
                        <td>
                            <button class="btn btn-primary" onclick="event.stopPropagation(); orderViewer.showOrderDetails(${order.id})" style="padding: 4px 8px; font-size: 12px;">
                                View
                            </button>
                        </td>
                    </tr>
                `;
            }).join('');

            // Mobile card view
            mobileContainer.innerHTML = orders.map(order => {
                const statusClass = `status-${order.status.toLowerCase()}`;
                const paidClass = order.is_paid ? 'paid-yes' : 'paid-no';
                const paidText = order.is_paid ? 'Yes' : 'No';
                const createdDate = new Date(order.created_at).toLocaleDateString();

                return `
                    <div class="mobile-order-card" onclick="orderViewer.showOrderDetails(${order.id})" data-order-id="${order.id}">
                        <div class="mobile-order-header">
                            <div class="mobile-order-id">#${order.id}</div>
                            <div class="mobile-order-status status-badge ${statusClass}">${order.status}</div>
                        </div>
                        
                        <div class="mobile-order-details">
                            <div class="mobile-order-detail">
                                <div class="mobile-order-label">Customer</div>
                                <div class="mobile-order-value">${this.escapeHtml(order.customer_name)}</div>
                            </div>
                            <div class="mobile-order-detail">
                                <div class="mobile-order-label">Total</div>
                                <div class="mobile-order-value">$${parseFloat(order.total).toFixed(2)}</div>
                            </div>
                            <div class="mobile-order-detail">
                                <div class="mobile-order-label">Email</div>
                                <div class="mobile-order-value">${this.escapeHtml(order.customer_email)}</div>
                            </div>
                            <div class="mobile-order-detail">
                                <div class="mobile-order-label">Payment Status</div>
                                <div class="mobile-order-value">
                                    <span class="paid-badge ${paidClass}">${paidText}</span>
                                </div>
                            </div>
                            <div class="mobile-order-detail" style="grid-column: 1 / -1;">
                                <div class="mobile-order-label">Created Date</div>
                                <div class="mobile-order-value">${createdDate}</div>
                            </div>
                        </div>
                        
                        <div class="mobile-order-actions">
                            <button class="btn btn-primary" onclick="event.stopPropagation(); orderViewer.showOrderDetails(${order.id})">
                                View Details
                            </button>
                        </div>
                    </div>
                `;
            }).join('');
        }

        renderStatistics(stats) {
            document.getElementById('total-orders').textContent = stats.total_orders || 0;
            document.getElementById('grand-total').textContent = stats.grand_total ? `$${parseFloat(stats.grand_total).toFixed(2)}` : '$0.00';
            document.getElementById('average-total').textContent = stats.average_total ? `$${parseFloat(stats.average_total).toFixed(2)}` : '$0.00';
            document.getElementById('paid-orders').textContent = stats.paid_orders || 0;
            document.getElementById('pending-orders').textContent = stats.pending_orders || 0;
            document.getElementById('processing-orders').textContent = stats.processing_orders || 0;
            document.getElementById('shipped-orders').textContent = stats.shipped_orders || 0;
            document.getElementById('cancelled-orders').textContent = stats.cancelled_orders || 0;
        }

        renderPagination(data) {
            const info = document.getElementById('pagination-info');
            const controls = document.getElementById('pagination-controls');

            // Pagination info
            const start = (data.current_page - 1) * data.per_page + 1;
            const end = Math.min(data.current_page * data.per_page, data.total);
            info.textContent = `Showing ${start}-${end} of ${data.total} orders`;

            // Pagination controls
            controls.innerHTML = '';

            if (data.prev_page_url) {
                const prevBtn = document.createElement('button');
                prevBtn.className = 'btn btn-secondary';
                prevBtn.textContent = 'Previous';
                prevBtn.onclick = () => this.goToPage(data.current_page - 1);
                controls.appendChild(prevBtn);
            }

            if (data.next_page_url) {
                const nextBtn = document.createElement('button');
                nextBtn.className = 'btn btn-secondary';
                nextBtn.textContent = 'Next';
                nextBtn.onclick = () => this.goToPage(data.current_page + 1);
                controls.appendChild(nextBtn);
            }
        }

        async goToPage(page) {
            this.currentPage = page;
            await this.loadOrders();
        }

        async showOrderDetails(orderId) {
            this.currentOrderId = orderId;

            try {
                const response = await fetch(`${window.API_BASE_URL}/orders/${orderId}`);
                const order = await response.json();

                if (response.ok) {
                    this.renderOrderDetails(order);
                    this.showModal();
                } else {
                    this.showError('Failed to load order details');
                }
            } catch (error) {
                console.error('Error loading order details:', error);
                this.showError('Network error occurred');
            }
        }

        renderOrderDetails(order) {
            const modalBody = document.getElementById('modal-body');
            const statusClass = `status-${order.status.toLowerCase()}`;
            const paidClass = order.is_paid ? 'paid-yes' : 'paid-no';
            const paidText = order.is_paid ? 'Yes' : 'No';
            const createdDate = new Date(order.created_at).toLocaleString();

            // Show/hide mark as paid button
            const markPaidBtn = document.getElementById('mark-paid-btn');
            markPaidBtn.style.display = order.is_paid ? 'none' : 'inline-flex';

            modalBody.innerHTML = `
                <div class="order-details">
                    <h4>Order Information</h4>
                    <div class="detail-grid">
                        <div class="detail-item">
                            <div class="detail-label">Order ID</div>
                            <div class="detail-value">#${order.id}</div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Customer Name</div>
                            <div class="detail-value">${this.escapeHtml(order.customer_name)}</div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Customer Email</div>
                            <div class="detail-value">${this.escapeHtml(order.customer_email)}</div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Status</div>
                            <div class="detail-value"><span class="status-badge ${statusClass}">${order.status}</span></div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Total Amount</div>
                            <div class="detail-value">$${parseFloat(order.total).toFixed(2)}</div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Payment Status</div>
                            <div class="detail-value"><span class="paid-badge ${paidClass}">${paidText}</span></div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Created Date</div>
                            <div class="detail-value">${createdDate}</div>
                        </div>
                    </div>                    <h4>Order Items</h4>
                    
                    <!-- Desktop Table View -->
                    <table class="items-table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Description</th>
                                <th>Quantity</th>
                                <th>Price</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${order.order_items.map(item => `
                                <tr>
                                    <td>${this.escapeHtml(item.product_name)}</td>
                                    <td>${this.escapeHtml(item.product_description || '-')}</td>
                                    <td>${item.quantity}</td>
                                    <td>$${parseFloat(item.price).toFixed(2)}</td>
                                    <td>$${parseFloat(item.total).toFixed(2)}</td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                    
                    <!-- Mobile Card View -->
                    <div class="mobile-items">
                        ${order.order_items.map(item => `
                            <div class="mobile-item-card">
                                <div class="mobile-item-header">${this.escapeHtml(item.product_name)}</div>
                                <div class="mobile-item-details">
                                    <div class="mobile-item-detail">
                                        <div class="mobile-item-label">Description</div>
                                        <div class="mobile-item-value">${this.escapeHtml(item.product_description || '-')}</div>
                                    </div>
                                    <div class="mobile-item-detail">
                                        <div class="mobile-item-label">Quantity</div>
                                        <div class="mobile-item-value">${item.quantity}</div>
                                    </div>
                                    <div class="mobile-item-detail">
                                        <div class="mobile-item-label">Unit Price</div>
                                        <div class="mobile-item-value">$${parseFloat(item.price).toFixed(2)}</div>
                                    </div>
                                    <div class="mobile-item-detail">
                                        <div class="mobile-item-label">Line Total</div>
                                        <div class="mobile-item-value">$${parseFloat(item.total).toFixed(2)}</div>
                                    </div>
                                </div>
                            </div>
                        `).join('')}
                    </div>
                </div>
            `;
        }

        async markOrderAsPaid() {
            if (!this.currentOrderId) return;

            try {
                const response = await fetch(`${window.API_BASE_URL}/orders/${this.currentOrderId}/mark-as-paid`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': window.Laravel.csrfToken
                    }
                });

                const result = await response.json();

                if (response.ok) {
                    this.showSuccess('Order marked as paid successfully');
                    this.closeModal();
                    this.refreshData();
                } else {
                    this.showError('Failed to mark order as paid');
                }
            } catch (error) {
                console.error('Error marking order as paid:', error);
                this.showError('Network error occurred');
            }
        }

        showModal() {
            document.getElementById('order-modal').classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        closeModal() {
            document.getElementById('order-modal').classList.remove('active');
            document.body.style.overflow = '';
            this.currentOrderId = null;
        }

        showError(message) {
            this.showMessage(message, 'error');
        }

        showSuccess(message) {
            this.showMessage(message, 'success');
        }

        showMessage(message, type) {
            const container = document.getElementById('message-container');
            const messageDiv = document.createElement('div');
            messageDiv.className = type;
            messageDiv.textContent = message;

            container.innerHTML = '';
            container.appendChild(messageDiv);

            setTimeout(() => {
                messageDiv.remove();
            }, 5000);
        }

        escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
    } // Toggle filters visibility for mobile
    function toggleFilters() {
        const content = document.getElementById('filter-content');
        const icon = document.getElementById('filter-toggle-icon');

        if (content.style.display === 'none') {
            content.style.display = 'block';
            icon.textContent = '▼';
        } else {
            content.style.display = 'none';
            icon.textContent = '▶';
        }
    }

    // Initialize filter visibility based on screen size
    function initializeFilters() {
        const content = document.getElementById('filter-content');
        const icon = document.getElementById('filter-toggle-icon');

        if (window.innerWidth <= 768) {
            content.style.display = 'none';
            icon.textContent = '▶';
        } else {
            content.style.display = 'block';
            icon.textContent = '▼';
        }
    } // Initialize the application when DOM is loaded
    let orderViewer;
    document.addEventListener('DOMContentLoaded', () => {
        initializeFilters();
        orderViewer = new OrderViewer();
    });

    // Handle window resize for filter visibility
    window.addEventListener('resize', initializeFilters);
</script>
@endsection