export function moveItem<T>(arr: T[], fromIndex: number, toIndex: number): T[] {
  const copy = [...arr];
  const [item] = copy.splice(fromIndex, 1);
  copy.splice(toIndex, 0, item);
  return copy;
}
