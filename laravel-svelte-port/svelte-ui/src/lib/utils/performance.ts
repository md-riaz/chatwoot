/**
 * Performance monitoring utilities for reports
 */

interface PerformanceMetric {
  name: string;
  startTime: number;
  endTime?: number;
  duration?: number;
}

class PerformanceMonitor {
  private metrics: Map<string, PerformanceMetric> = new Map();
  private enabled: boolean = import.meta.env.DEV;

  start(name: string): void {
    if (!this.enabled) return;
    
    this.metrics.set(name, {
      name,
      startTime: performance.now()
    });
  }

  end(name: string): number | null {
    if (!this.enabled) return null;
    
    const metric = this.metrics.get(name);
    if (!metric) {
      console.warn(`Performance metric "${name}" not found`);
      return null;
    }

    const endTime = performance.now();
    const duration = endTime - metric.startTime;
    
    metric.endTime = endTime;
    metric.duration = duration;
    
    console.log(`⚡ ${name}: ${duration.toFixed(2)}ms`);
    
    return duration;
  }

  measure<T>(name: string, fn: () => T): T {
    if (!this.enabled) return fn();
    
    this.start(name);
    const result = fn();
    this.end(name);
    
    return result;
  }

  async measureAsync<T>(name: string, fn: () => Promise<T>): Promise<T> {
    if (!this.enabled) return fn();
    
    this.start(name);
    const result = await fn();
    this.end(name);
    
    return result;
  }

  getMetrics(): PerformanceMetric[] {
    return Array.from(this.metrics.values()).filter(m => m.duration !== undefined);
  }

  clear(): void {
    this.metrics.clear();
  }

  report(): void {
    if (!this.enabled) return;
    
    const metrics = this.getMetrics();
    if (metrics.length === 0) return;
    
    console.group('📊 Performance Report');
    metrics.forEach(metric => {
      console.log(`${metric.name}: ${metric.duration!.toFixed(2)}ms`);
    });
    console.groupEnd();
  }
}

export const performanceMonitor = new PerformanceMonitor();

/**
 * Decorator for measuring function performance
 */
export function measurePerformance(name?: string) {
  return function (target: any, propertyKey: string, descriptor: PropertyDescriptor) {
    const originalMethod = descriptor.value;
    const metricName = name || `${target.constructor.name}.${propertyKey}`;
    
    descriptor.value = function (...args: any[]) {
      return performanceMonitor.measure(metricName, () => originalMethod.apply(this, args));
    };
    
    return descriptor;
  };
}

/**
 * Utility for measuring component render time
 */
export function measureRender(componentName: string) {
  return {
    start: () => performanceMonitor.start(`${componentName} render`),
    end: () => performanceMonitor.end(`${componentName} render`)
  };
}