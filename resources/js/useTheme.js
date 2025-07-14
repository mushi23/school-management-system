import { ref, watch, onMounted } from 'vue';

export function useTheme() {
  const theme = ref('light'); // default light

  // Apply the theme class to <html> tag
  const applyTheme = (value) => {
    const html = document.documentElement;
    if (value === 'dark') {
      html.classList.add('dark');
    } else {
      html.classList.remove('dark');
    }
  };

  // Initialize theme automatically on mount
  onMounted(() => {
    const storedTheme = localStorage.getItem('theme');
    if (storedTheme === 'dark' || storedTheme === 'light') {
      theme.value = storedTheme;
    } else {
      theme.value = 'dark'; // Force dark
    }
    applyTheme(theme.value);
  });

  // Watch theme changes, update class and localStorage
  watch(theme, (newTheme) => {
    applyTheme(newTheme);
    localStorage.setItem('theme', newTheme);
  });

  const toggleTheme = () => {
    theme.value = theme.value === 'dark' ? 'light' : 'dark';
  };

  return {
    theme,
    toggleTheme,
  };
}
