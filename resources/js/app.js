import Alpine from 'alpinejs';
import { registerDataTables } from './datatables';
import { registerTipTap } from './tiptap';

window.Alpine = Alpine;

registerDataTables(Alpine);
registerTipTap(Alpine);

Alpine.start();
