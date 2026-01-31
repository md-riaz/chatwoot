<script lang="ts">
  import { Button } from '$lib/components/ui/button';
  import * as Card from '$lib/components/ui/card';
  import * as Avatar from '$lib/components/ui/avatar';
  import { Globe, Building, Users, Calendar } from 'lucide-svelte';
  import type { Company } from '$lib/api/companies';

  interface Props {
    company: Company;
    onView?: (id: number) => void;
    onEdit?: (company: Company) => void;
    onDelete?: (id: number, name: string) => void;
  }

  let { company, onView, onEdit, onDelete }: Props = $props();

  function formatDate(dateString: string) {
    return new Date(dateString).toLocaleDateString();
  }

  function isValidUrl(url: string): boolean {
    try {
      const urlObj = new URL(url.startsWith('http') ? url : `https://${url}`);
      return urlObj.protocol === 'http:' || urlObj.protocol === 'https:';
    } catch {
      return false;
    }
  }

  function handleView(e: MouseEvent) {
    e.stopPropagation();
    onView?.(company.id);
  }

  function handleEdit(e: MouseEvent) {
    e.stopPropagation();
    onEdit?.(company);
  }

  function handleDelete(e: MouseEvent) {
    e.stopPropagation();
    onDelete?.(company.id, company.name);
  }
</script>

<Card.Root
  class="hover:shadow-md transition-shadow cursor-pointer group"
  onclick={() => onView?.(company.id)}
>
  <Card.Content class="p-6">
    <div class="flex items-start gap-4">
      <Avatar.Root class="h-12 w-12">
        <!-- Add avatar image if available in company interface in future -->
        <Avatar.Fallback>
          {company.name.substring(0, 2).toUpperCase()}
        </Avatar.Fallback>
      </Avatar.Root>

      <div class="flex-1 min-w-0">
        <div class="flex items-center justify-between mb-2">
          <h3 class="font-semibold text-lg truncate pr-2">{company.name}</h3>

          <!-- Actions visible on hover or focus -->
          <div
            class="flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity"
          >
            <Button
              variant="ghost"
              size="sm"
              class="h-8 px-2"
              onclick={handleEdit}
            >
              Edit
            </Button>
            <Button
              variant="ghost"
              size="sm"
              class="h-8 px-2 text-destructive hover:text-destructive"
              onclick={handleDelete}
            >
              Delete
            </Button>
          </div>
        </div>

        {#if company.domain}
          <div
            class="flex items-center gap-2 text-sm text-muted-foreground mb-2"
          >
            <Globe class="h-3.5 w-3.5 shrink-0" />
            <a
              href={isValidUrl(company.domain)
                ? company.domain
                : `https://${company.domain}`}
              target="_blank"
              rel="noopener noreferrer"
              class="hover:underline truncate"
              onclick={e => e.stopPropagation()}
            >
              {company.domain}
            </a>
          </div>
        {/if}

        {#if company.description}
          <p class="text-sm text-muted-foreground mb-3 line-clamp-2">
            {company.description}
          </p>
        {/if}

        <div
          class="flex flex-wrap gap-x-4 gap-y-2 text-xs text-muted-foreground mt-auto pt-2 border-t"
        >
          {#if company.industry}
            <div class="flex items-center gap-1.5">
              <Building class="h-3.5 w-3.5" />
              <span>{company.industry}</span>
            </div>
          {/if}

          {#if company.employees}
            <div class="flex items-center gap-1.5">
              <Users class="h-3.5 w-3.5" />
              <span>{company.employees} employees</span>
            </div>
          {/if}

          <div class="flex items-center gap-1.5 ml-auto">
            <Calendar class="h-3.5 w-3.5" />
            <span>Added {formatDate(company.created_at)}</span>
          </div>
        </div>
      </div>
    </div>
  </Card.Content>
</Card.Root>
