# Translation Manager Layout Template

## Overview

This is a standalone layout template for the CakePHP Translate plugin with a modern, responsive design.

## Features

### Included Libraries

- **Bootstrap 5.3.3** - Modern CSS framework with responsive grid system
- **jQuery 3.7.1** - JavaScript library for DOM manipulation
- **Font Awesome 6.7.2** - Comprehensive icon set (6,000+ icons)
- **Flag Icons 7.2.3** - SVG flag icons for language representation

### Design Features

- **Modern UI** - Clean, professional design with gradient navbar
- **Fully Responsive** - Mobile-first design that works on all devices
- **Flexbox Layout** - Sticky footer that stays at the bottom
- **Custom Styling** - CSS variables for easy theme customization
- **Box Shadows** - Subtle depth and elevation effects
- **Smooth Transitions** - Animated button hovers and interactions

### Built-in JavaScript Functionality

1. **Form Loading States** - Submit buttons show spinner and disable on form submission
2. **Delete Confirmations** - Automatic confirmation dialogs for delete actions
3. **Bootstrap Components** - Auto-initialization of tooltips and popovers
4. **Custom Scrollbar** - Styled scrollbar for webkit browsers

### Template Blocks

The layout supports the following CakePHP template blocks:

- `title` - Page title (shown in browser tab)
- `meta` - Additional meta tags
- `css` - Custom CSS files
- `content` - Main page content (required)
- `script` - Custom JavaScript code

### Usage Example

```php
// In your view file
$this->assign('title', 'My Page Title');
?>

<div class="page-header">
    <h1><?= __('My Page') ?></h1>
</div>

<div class="card">
    <div class="card-header">
        <i class="fas fa-info-circle"></i> Information
    </div>
    <div class="card-body">
        <p>Your content here...</p>
    </div>
</div>

<?php $this->start('script'); ?>
<script>
    // Your custom JavaScript
</script>
<?php $this->end(); ?>
```

### Available CSS Classes

#### Helper Classes
- `.defaulting` - Gray italic text for default/placeholder content
- `.highlight` - Yellow highlight background
- `.loading` - Makes element semi-transparent and non-interactive
- `.page-header` - Styled page header with bottom border

#### Font Awesome Icons
Use any FA6 icon with class format: `<i class="fas fa-icon-name"></i>`

Examples:
- `<i class="fas fa-language"></i>` - Language icon
- `<i class="fas fa-globe"></i>` - Globe icon
- `<i class="fas fa-check"></i>` - Check icon
- `<i class="fas fa-times"></i>` - Close icon

### Customization

#### Colors
Modify CSS variables in the `:root` selector:

```css
:root {
    --primary-color: #0d6efd;
    --secondary-color: #6c757d;
    --success-color: #198754;
    --danger-color: #dc3545;
    --warning-color: #ffc107;
    --info-color: #0dcaf0;
    --light-bg: #f8f9fa;
    --dark-text: #212529;
    --border-color: #dee2e6;
}
```

#### Navbar Gradient
Change the navbar background gradient:

```css
.navbar {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}
```

### Browser Support

- Chrome (latest)
- Firefox (latest)
- Safari (latest)
- Edge (latest)
- Mobile browsers (iOS Safari, Chrome Mobile)

### CDN Dependencies

All external resources are loaded from CDNs with SRI (Subresource Integrity) hashes for security:

- Bootstrap CSS/JS: cloudflare.com
- jQuery: cloudflare.com
- Font Awesome: cloudflare.com
- Flag Icons: jsdelivr.net

### Accessibility

- Semantic HTML5 elements
- ARIA labels on interactive elements
- Keyboard navigation support
- Screen reader friendly
- Sufficient color contrast ratios

### Performance

- Minimal external dependencies
- Optimized CSS with modern features
- Lazy loading of tooltips/popovers
- Hardware-accelerated animations
