/**
 * Global helper functions for Vue components
 */

/**
 * Get initials from a name
 * @param {string} name - Full name
 * @param {number} length - Number of initials to return (default: 2)
 * @returns {string} Uppercase initials
 */
export const getInitials = (name, length = 2) => {
    if (!name) return '?';
    return name
        .split(' ')
        .map(word => word.charAt(0))
        .join('')
        .substring(0, length)
        .toUpperCase();
};

/**
 * Format date to Indonesian locale
 * @param {string|Date} date - Date to format
 * @param {object} options - Intl.DateTimeFormat options
 * @returns {string} Formatted date string
 */
export const formatDate = (date, options = {}) => {
    if (!date) return '-';
    
    const defaultOptions = { 
        day: 'numeric', 
        month: 'long', 
        year: 'numeric' 
    };
    
    return new Date(date).toLocaleDateString('id-ID', { ...defaultOptions, ...options });
};

/**
 * Format date with time to Indonesian locale
 * @param {string|Date} date - Date to format
 * @returns {string} Formatted date string with time
 */
export const formatDateTime = (date) => {
    if (!date) return '-';
    
    return new Date(date).toLocaleDateString('id-ID', { 
        day: 'numeric', 
        month: 'long', 
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

/**
 * Format date short (e.g., "29 Des 2024")
 * @param {string|Date} date - Date to format
 * @returns {string} Short formatted date
 */
export const formatDateShort = (date) => {
    if (!date) return '-';
    
    return new Date(date).toLocaleDateString('id-ID', { 
        day: 'numeric', 
        month: 'short', 
        year: 'numeric' 
    });
};

/**
 * Format relative time (e.g., "2 hari yang lalu")
 * @param {string|Date} date - Date to format
 * @returns {string} Relative time string
 */
export const formatRelativeTime = (date) => {
    if (!date) return '-';
    
    const now = new Date();
    const then = new Date(date);
    const diffMs = now - then;
    const diffSecs = Math.floor(diffMs / 1000);
    const diffMins = Math.floor(diffSecs / 60);
    const diffHours = Math.floor(diffMins / 60);
    const diffDays = Math.floor(diffHours / 24);
    
    if (diffDays > 30) {
        return formatDateShort(date);
    } else if (diffDays > 0) {
        return `${diffDays} hari yang lalu`;
    } else if (diffHours > 0) {
        return `${diffHours} jam yang lalu`;
    } else if (diffMins > 0) {
        return `${diffMins} menit yang lalu`;
    } else {
        return 'Baru saja';
    }
};

/**
 * Get document/file URL with storage path handling
 * @param {string} path - File path
 * @returns {string|null} Full URL or null
 */
export const getDocumentUrl = (path) => {
    if (!path) return null;
    return path.startsWith('/') ? path : `/storage/${path}`;
};

/**
 * Get avatar URL or return null for initials fallback
 * @param {object} user - User object with avatar field
 * @returns {string|null} Avatar URL or null
 */
export const getAvatarUrl = (user) => {
    if (!user?.avatar) return null;
    if (user.avatar.startsWith('http')) return user.avatar;
    return user.avatar.startsWith('/') ? user.avatar : `/storage/${user.avatar}`;
};
