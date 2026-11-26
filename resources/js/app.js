import Alpine from 'alpinejs';
import { registerDataTables } from './datatables';
import { registerTipTap } from './tiptap';
import { registerNotify } from './alert';

window.Alpine = Alpine;

registerNotify();
registerDataTables(Alpine);
registerTipTap(Alpine);

Alpine.start();
