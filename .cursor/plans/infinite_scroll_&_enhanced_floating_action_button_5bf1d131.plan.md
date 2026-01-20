---
name: Infinite Scroll & Enhanced Floating Action Button
overview: Implement infinite scroll for marketplace products page and enhance FloatingActionButton to support both creating posts and uploading marketplace products with a menu selector.
todos:
  - id: infinite-scroll-marketplace
    content: Implement infinite scroll for Marketplace Products page (remove pagination, add Intersection Observer, loading indicator)
    status: completed
  - id: floating-action-button-menu
    content: Enhance FloatingActionButton with expandable menu for Create Post and Add Product actions
    status: completed
  - id: create-product-modal
    content: Create CreateProductModal component with form fields, file upload, and validation
    status: completed
  - id: infinite-scroll-home
    content: Implement infinite scroll for Home page PostFeed (bonus feature)
    status: completed
---

# Infinite Scroll & Enhanced Floating Action Button

## Overview

Replace traditional pagination (1, 2, 3...) with infinite scroll on marketplace products page, and enhance the FloatingActionButton to provide quick access to both "Create Post" and "Add Product" actions via a menu interface.

## Part 1: Infinite Scroll for Marketplace Products

### Current Implementation

- Uses Laravel pagination with page links (1, 2, 3...)
- Controller: `ProductController@index` returns paginated results (12 items per page)
- View: `Marketplace/Index.vue` displays pagination links

### Implementation Strategy

**Backend Changes:File: `app/Http/Controllers/Marketplace/ProductController.php`**

1. Add support for AJAX requests to return JSON when requested
2. Maintain existing pagination structure (Inertia compatible)
3. Check for `X-Inertia` header or `page` query parameter

**Approach:** Use Inertia's `only()` method to handle both full page loads and partial updates, allowing the same endpoint to work for initial load and infinite scroll.**Frontend Changes:File: `resources/js/Pages/Marketplace/Index.vue`**

1. Remove pagination links UI
2. Add scroll detection using Intersection Observer API or scroll event
3. Load next page when user scrolls near bottom
4. Use Inertia's `router.reload()` or `router.visit()` with `only` to append data
5. Add loading indicator while fetching next page
6. Disable loading when no more pages available

**Implementation Details:**

- Use Intersection Observer to detect when bottom sentinel element is visible
- Track current page number
- Accumulate products in a reactive array
- Show loading spinner at bottom while fetching
- Handle edge cases: no more pages, network errors, search/filter changes

## Part 2: Enhanced Floating Action Button

### Current Implementation

- Single button opens CreatePostModal
- Component: `FloatingActionButton.vue`
- Always shows same icon (+)

### Implementation Strategy

**New Design:**

- Main button expands to show two options: "Create Post" and "Add Product"
- Use animated menu (expand/collapse) or modal with quick actions
- Show different icons for each action

**Option A: Expandable Menu (Recommended)**

- Click main button → shows two action buttons
- Click action → opens respective modal
- Click outside or same button → collapses menu
- Smooth animations (scale, fade)

**Option B: Modal Menu**

- Click main button → shows modal with two large action cards
- User selects action → closes menu modal, opens action modal

**Files to Create/Modify:File: `resources/js/Components/FloatingActionButton.vue`**

1. Add state for menu open/closed
2. Add two action buttons (Post & Product)
3. Import CreatePostModal (existing)
4. Create CreateProductModal component reference
5. Add animation classes for expand/collapse
6. Handle click outside to close menu
7. Update button to show menu icon when closed, close icon when open

**File: `resources/js/Components/CreateProductModal.vue` (New)**

1. Similar structure to CreatePostModal
2. Include product form fields (name, description, price, category, image, file_download)
3. File upload handling with size validation
4. Submit to `marketplace.products.store` route
5. Handle success/error responses
6. Close modal on success

**Alternative:** Instead of modal, can use Inertia navigation to product create page, but modal provides better UX.

## Implementation Details

### Infinite Scroll Implementation

**File: `resources/js/Pages/Marketplace/Index.vue`**

```javascript
// Key additions:
const productsList = ref([])
const currentPage = ref(1)
const hasMorePages = ref(true)
const isLoading = ref(false)

// Load more function
const loadMore = () => {
  if (isLoading.value || !hasMorePages.value) return
  
  isLoading.value = true
  router.reload({
    only: ['products'],
    data: { page: currentPage.value + 1 },
    preserveState: true,
    preserveScroll: true,
    onSuccess: (page) => {
      // Append new products
      productsList.value.push(...page.props.products.data)
      currentPage.value++
      hasMorePages.value = page.props.products.next_page_url !== null
      isLoading.value = false
    }
  })
}

// Intersection Observer for scroll detection
// Trigger loadMore when sentinel element visible
```

**Considerations:**

- Reset scroll state when search/filter changes
- Handle URL query parameters (search, category) in loadMore
- Preserve filters when loading next page

### Floating Action Button Enhancement

**File: `resources/js/Components/FloatingActionButton.vue`**

```javascript
// State
const isMenuOpen = ref(false)
const showPostModal = ref(false)
const showProductModal = ref(false)

// Methods
const toggleMenu = () => {
  isMenuOpen.value = !isMenuOpen.value
}

const openPostModal = () => {
  showPostModal.value = true
  isMenuOpen.value = false
}

const openProductModal = () => {
  showProductModal.value = true
  isMenuOpen.value = false
}
```

**UI Structure:**

```javascript
[Main Button] → Click → [Menu Expands]
                           ├─ [Create Post Button]
                           └─ [Add Product Button]
```



## Files to Modify

### Backend

1. `app/Http/Controllers/Marketplace/ProductController.php`

- Ensure pagination works with `page` query parameter
- Handle `only` requests for Inertia partial reloads

### Frontend

1. `resources/js/Pages/Marketplace/Index.vue`

- Remove pagination links (lines 65-84)
- Add infinite scroll logic
- Add loading indicator
- Add sentinel element for scroll detection

2. `resources/js/Components/FloatingActionButton.vue`

- Add menu state management
- Add two action buttons with icons
- Add expand/collapse animations
- Import and integrate CreateProductModal

3. `resources/js/Components/CreateProductModal.vue` (New)

- Create new modal component
- Form fields matching ProductController validation
- File upload handling
- Submit handling

## UX Considerations

### Infinite Scroll

- Show subtle loading indicator at bottom while loading
- Smooth scroll behavior
- Reset to top when filters/search change
- Handle "no more products" state gracefully

### Floating Action Button

- Menu should appear near main button (above or to the side)
- Smooth animations (200-300ms)
- Clear visual distinction between actions
- Accessible (keyboard navigation, ARIA labels)
- Mobile-friendly (touch targets)

## Testing Checklist

### Infinite Scroll

- [x] Loads next page on scroll
- [x] Accumulates products correctly
- [x] Handles end of results (no more pages)
- [x] Resets when search/filter changes