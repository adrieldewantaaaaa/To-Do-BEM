export const statusLabel = (status) =>
  ({
    todo: 'To-do',
    in_progress: 'In progress',
    done: 'Done',
    active: 'Active',
    completed: 'Completed',
    archived: 'Archived',
  })[status] ?? status;
export const priorityLabel = (priority) => ({ low: 'Low', medium: 'Medium', high: 'High' })[priority] ?? priority;
export const formatDate = (value, options = {}) =>
  value
    ? new Intl.DateTimeFormat('en', {
        day: 'numeric',
        month: 'short',
        year: options.short ? undefined : 'numeric',
      }).format(new Date(`${String(value).slice(0, 10)}T00:00:00`))
    : '—';
export const isOverdue = (task) =>
  task.status !== 'done' &&
  new Date(`${String(task.deadline).slice(0, 10)}T00:00:00`) < new Date(new Date().toDateString());
