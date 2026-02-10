<script lang="ts">
  interface Props {
    size?: 'sm' | 'md';
    pulse?: boolean;
  }
  
  let { size = 'md', pulse = true }: Props = $props();
  
  const sizeClasses = {
    sm: 'py-0.5 px-1.5 text-[10px]',
    md: 'py-0.5 px-2 text-xs'
  };
  
  const dotSizeClasses = {
    sm: 'h-0.5 w-0.5',
    md: 'h-1 w-1'
  };
</script>

<span class="flex flex-row items-center rounded bg-teal-100 dark:bg-teal-900/30 {sizeClasses[size]}">
  <span 
    class="bg-teal-600 dark:bg-teal-400 rounded-full mr-1 rtl:mr-0 rtl:ml-1 {dotSizeClasses[size]} {pulse ? 'animate-pulse' : ''}"
  ></span>
  <span class="text-teal-700 dark:text-teal-300 font-medium">
    LIVE
  </span>
</span>