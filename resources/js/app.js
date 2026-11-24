import Alpine from 'alpinejs';
import { registerDataTables } from './datatables';

window.Alpine = Alpine;

registerDataTables(Alpine);

Alpine.start();
