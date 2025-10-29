<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Manage Menu</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>

    <?php require_once 'sidebar.php'; ?>

    <div class="main-content">

        <header>
            <h1>Manage Menu</h1>
            <!-- The back button is in the sidebar now -->
        </header>

        <main class="admin-layout">

            <!-- Column 1: Add New Items/Categories -->
            <section class="admin-form-container">
                <!-- Add Category Form -->
                <h3>Add New Category</h3>
                <form id="add-category-form" class="admin-form">
                    <label for="category-name">Category Name:</label>
                    <input type="text" id="category-name" name="category_name" required>
                    <button type="submit">Add Category</button>
                </form>

                <hr class="form-divider">

                <!-- Add Menu Item Form -->
                <h3>Add New Menu Item</h3>
                <form id="add-item-form" class="admin-form">
                    <label for="item-name">Item Name:</label>
                    <input type="text" id="item-name" name="item_name" required>
                    
                    <label for="item-price">Price (MYR):</label>
                    <input type="number" id="item-price" name="price" step="0.01" min="0" required>
                    
                    <label for="item-category">Category:</label>
                    <select id="item-category" name="category_id" required>
                        <option value="">Loading categories...</option>
                    </select>
                    
                    <label for="item-desc">Description (Optional):</label>
                    <textarea id="item-desc" name="description" rows="3"></textarea>
                    
                    <button type="submit">Add Item</button>
                </form>
            </section>

            <!-- Column 2: Existing Menu -->
            <section class="admin-list-container">
                <h2>Existing Menu</h2>
                <div id="existing-menu-list">
                    <!-- Full menu will be loaded here -->
                </div>
            </section>

        </main>

    </div> <!-- End .main-content -->

    <!-- ==== EDIT ITEM MODAL (New) ==== -->
    <div id="edit-modal" class="modal-backdrop" style="display: none;">
        <div class="modal-content">
            <h2>Edit Menu Item</h2>
            <form id="edit-item-form" class="admin-form">
                <!-- Hidden input to store the item ID -->
                <input type="hidden" id="edit_item_id" name="edit_item_id">

                <label for="edit_item_name">Item Name:</label>
                <input type="text" id="edit_item_name" name="edit_item_name" required>
                
                <label for="edit_item_price">Price (MYR):</label>
                <input type="number" id="edit_item_price" name="edit_item_price" step="0.01" min="0" required>
                
                <label for="edit_item_category">Category:</label>
                <select id="edit_item_category" name="edit_item_category" required>
                    <!-- Categories will be loaded here by JS -->
                </select>
                
                <label for="edit_item_desc">Description (Optional):</label>
                <textarea id="edit_item_desc" name="edit_item_desc" rows="3"></textarea>
                
                <div class="modal-actions">
                    <button type="submit" class="btn-save">Save Changes</button>
                    <button type="button" class="btn-cancel" onclick="closeEditModal()">Cancel</button>
                </div>
            </form>
        </div>
    </div>


    <script>
        // Global variable to store categories
        let categories = [];

        // Function to load the full menu and categories
        async function loadAdminMenu() {
            try {
                const response = await fetch('api/get_full_menu.php');
                const menuData = await response.json();
                
                const menuList = document.getElementById('existing-menu-list');
                const categorySelect = document.getElementById('item-category');
                const editCategorySelect = document.getElementById('edit_item_category');
                
                menuList.innerHTML = '';
                categorySelect.innerHTML = '<option value="">-- Select a Category --</option>';
                editCategorySelect.innerHTML = '<option value="">-- Select a Category --</option>';
                categories = menuData.categories; // Store categories

                // Populate the <select> dropdowns
                categories.forEach(cat => {
                    const option = document.createElement('option');
                    option.value = cat.category_id;
                    option.textContent = cat.category_name;
                    categorySelect.appendChild(option);
                    // Clone the option for the edit modal
                    editCategorySelect.appendChild(option.cloneNode(true));
                });

                // Populate the "Existing Menu" list
                menuData.menu.forEach(cat => {
                    if (cat.items.length === 0) return; // Skip empty
                    
                    const catDiv = document.createElement('div');
                    catDiv.className = 'menu-category-admin';
                    catDiv.innerHTML = `<h3>${cat.category_name}</h3>`;
                    
                    const table = document.createElement('table');
                    table.className = 'admin-item-table';
                    table.innerHTML = `
                        <tr>
                            <th>Item</th>
                            <th>Price</th>
                            <th>Available</th>
                            <th>Action</th>
                        </tr>
                    `;
                    
                    // --- THIS IS THE ROBUST FIX ---
                    // We create elements manually instead of using innerHTML
                    // This correctly preserves the 'item' object for the edit button
                    cat.items.forEach(item => {
                        const row = document.createElement('tr');
                        row.id = `item-row-${item.item_id}`; 

                        // Cell 1: Item Name
                        const cellName = document.createElement('td');
                        cellName.textContent = item.item_name;
                        row.appendChild(cellName);

                        // Cell 2: Price
                        const cellPrice = document.createElement('td');
                        cellPrice.textContent = `MYR ${parseFloat(item.price).toFixed(2)}`;
                        row.appendChild(cellPrice);

                        // Cell 3: Availability Checkbox
                        const cellAvailable = document.createElement('td');
                        const checkbox = document.createElement('input');
                        checkbox.type = 'checkbox';
                        checkbox.checked = item.is_available;
                        // Use an arrow function for the event listener
                        checkbox.onchange = () => toggleAvailability(item.item_id, checkbox.checked);
                        cellAvailable.appendChild(checkbox);
                        row.appendChild(cellAvailable);

                        // Cell 4: Action Buttons
                        const cellActions = document.createElement('td');
                        cellActions.className = 'action-buttons';

                        // Edit Button
                        const editButton = document.createElement('button');
                        editButton.className = 'btn-edit';
                        editButton.textContent = 'Edit';
                        // This is the key change: add listener with closure
                        // This passes the actual 'item' object, not a string
                        editButton.onclick = () => openEditModal(item); 
                        cellActions.appendChild(editButton);

                        // Delete Button
                        const deleteButton = document.createElement('button');
                        deleteButton.className = 'btn-delete';
                        deleteButton.textContent = 'Delete';
                        deleteButton.onclick = () => deleteMenuItem(item.item_id);
                        cellActions.appendChild(deleteButton);

                        row.appendChild(cellActions);

                        // Finally, append the row to the table
                        table.appendChild(row);
                    });
                    // --- END OF ROBUST FIX ---
                    
                    catDiv.appendChild(table);
                    menuList.appendChild(catDiv);
                });

            } catch (error) {
                console.error('Error loading menu:', error);
            }
        }

        // --- NEW: Edit Modal Functions ---
        function openEditModal(item) {
            // Populate the form fields
            document.getElementById('edit_item_id').value = item.item_id;
            document.getElementById('edit_item_name').value = item.item_name;
            document.getElementById('edit_item_price').value = item.price;
            document.getElementById('edit_item_category').value = item.category_id;
            document.getElementById('edit_item_desc').value = item.description;
            
            // Show the modal
            document.getElementById('edit-modal').style.display = 'flex';
        }

        function closeEditModal() {
            document.getElementById('edit-modal').style.display = 'none';
        }

        // Handle the edit form submission
        document.getElementById('edit-item-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            const form = e.target;
            const formData = new FormData(form);

            try {
                const response = await fetch('api/update_menu_item.php', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();
                
                if (result.success) {
                    alert('Item updated successfully!');
                    closeEditModal();
                    loadAdminMenu(); // Refresh the list
                } else {
                    alert('Error updating item: ' + result.error);
                }
            } catch (error) {
                console.error('Error:', error);
            }
        });

        // --- NEW: Delete Item Function ---
        async function deleteMenuItem(itemId) {
            if (!confirm('Are you sure you want to delete this menu item?\nThis action cannot be undone.')) {
                return;
            }

            try {
                const response = await fetch('api/delete_menu_item.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ item_id: itemId })
                });
                const result = await response.json();

                if (result.success) {
                    alert('Item deleted successfully.');
                    // Remove the item row from the table
                    document.getElementById(`item-row-${itemId}`).remove();
                } else {
                    alert('Error deleting item: ' + result.error);
                }
            } catch (error) {
                console.error('Error:', error);
            }
        }


        // Function to handle adding a new category
        document.getElementById('add-category-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            const form = e.target;
            const formData = new FormData(form);

            try {
                const response = await fetch('api/add_category.php', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();
                
                if (result.success) {
                    alert('Category added!');
                    form.reset();
                    loadAdminMenu(); // Refresh the menu
                } else {
                    alert('Error: ' + result.error);
                }
            } catch (error) {
                console.error('Error:', error);
            }
        });

        // Function to handle adding a new menu item
        document.getElementById('add-item-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            const form = e.target;
            const formData = new FormData(form);

            try {
                const response = await fetch('api/add_menu_item.php', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();
                
                if (result.success) {
                    alert('Menu item added!');
                    form.reset();
                    loadAdminMenu(); // Refresh the menu
                } else {
                    alert('Error: Something went wrong. ' + result.error);
                }
            } catch (error) {
                console.error('Error:', error);
            }
        });

        // Function to toggle item availability
        async function toggleAvailability(itemId, isAvailable) {
            try {
                const response = await fetch('api/update_item_availability.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ item_id: itemId, is_available: isAvailable })
                });
                const result = await response.json();
                if (!result.success) {
                    alert('Error updating item: ' + result.error);
                }
            } catch (error) {
                console.error('Error:', error);
            }
        }

        // Load the menu when the page is ready
        document.addEventListener('DOMContentLoaded', loadAdminMenu);
    </script>
</body>
</html>

