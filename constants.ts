

import { ElectricityRecord, WasteRecord, WaterRecord, User } from './types';

export const INITIAL_UNITS = ['Galpão 6', 'Galpão 7', 'Galpão 20', 'Galpão 21'];
export const INITIAL_GOAL = 40; // Electricity % Goal
export const INITIAL_WATER_GOAL = 40; // Water Max Volume Goal per Unit (m3)
export const INITIAL_WASTE_GOAL = 90; // Recycling Rate Goal (%)

export const INITIAL_USERS: User[] = [
  {
    id: '1',
    name: 'Administrador',
    email: 'admin@ctdi.com',
    password: 'admin',
    role: 'manager',
    allowedUnits: INITIAL_UNITS // Admin sees all
  },
  {
    id: '2',
    name: 'Visualizador',
    email: 'user@ctdi.com',
    password: 'user',
    role: 'viewer',
    allowedUnits: ['Galpão 20', 'Galpão 21'] // Restricted view example
  }
];

const createElecRecord = (month: number, unit: string, cpflKwh: number, cpflCost: number, floraKwh: number, floraCost: number, floraSavings: number = 0): ElectricityRecord => ({
  id: crypto.randomUUID(),
  date: `2024-${month.toString().padStart(2, '0')}-01`,
  unit,
  cpflKwh,
  cpflCost,
  floraKwh,
  floraCost,
  floraSavings
});

export const INITIAL_ELECTRICITY: ElectricityRecord[] = [
  // Galpão 6
  createElecRecord(1, 'Galpão 6', 1585, 1445.18, 0, 0, 0),
  createElecRecord(2, 'Galpão 6', 1938, 1767.16, 0, 0, 0),
  createElecRecord(3, 'Galpão 6', 2264, 232.28, 0, 0, 0),
  createElecRecord(4, 'Galpão 6', 2238, 2026.89, 0, 0, 0),
  createElecRecord(5, 'Galpão 6', 2022, 1657.06, 0, 0, 0),
  createElecRecord(6, 'Galpão 6', 2010, 1851.22, 0, 0, 0),
  createElecRecord(7, 'Galpão 6', 2559, 2385.68, 0, 0, 0),
  createElecRecord(8, 'Galpão 6', 2052, 1828.79, 0, 0, 0),
  createElecRecord(9, 'Galpão 6', 2269, 2261.88, 0, 0, 0),
  createElecRecord(10, 'Galpão 6', 100, 344.03, 274.02, 1681.21, 2464.00),

  // Galpão 7
  createElecRecord(1, 'Galpão 7', 1618, 1474.82, 0, 0, 0),
  createElecRecord(2, 'Galpão 7', 1728, 1577.92, 0, 0, 0),
  createElecRecord(3, 'Galpão 7', 3722, 1495.15, 0, 0, 0),
  createElecRecord(4, 'Galpão 7', 3617, 3262.93, 0, 0, 0),
  createElecRecord(5, 'Galpão 7', 3344, 2727.92, 0, 0, 0),
  createElecRecord(6, 'Galpão 7', 2806, 2576.04, 0, 0, 0),
  createElecRecord(7, 'Galpão 7', 3527, 3280.58, 0, 0, 0),
  createElecRecord(8, 'Galpão 7', 2734, 2424.02, 0, 0, 0),
  createElecRecord(9, 'Galpão 7', 3179, 3160.61, 0, 0, 0),
  createElecRecord(10, 'Galpão 7', 3642, 442.79, 0, 0, 0),

  // Galpão 20
  createElecRecord(1, 'Galpão 20', 5503, 5132.19, 191, 1402.88, 1964.00),
  createElecRecord(2, 'Galpão 20', 5394, 5050.75, 196, 1405.35, 1966.00),
  createElecRecord(3, 'Galpão 20', 7175, 4677.32, 187, 1402.70, 1964.00),
  createElecRecord(4, 'Galpão 20', 6039, 5603.17, 187.06, 1397.70, 1957.00),
  createElecRecord(5, 'Galpão 20', 100, 0, 615.56, 4846.06, 6808.00),
  createElecRecord(6, 'Galpão 20', 100, 779.37, 786.99, 5046.42, 7401.00),
  createElecRecord(7, 'Galpão 20', 100, 1163.73, 700.64, 6328.32, 9291.00),
  createElecRecord(8, 'Galpão 20', 100, 622.36, 156.84, 3910.02, 5746.00),
  createElecRecord(9, 'Galpão 20', 100, 1163.58, 264.94, 4415.64, 6472.00),
  createElecRecord(10, 'Galpão 20', 100, 1722.07, 1205, 5503.14, 9392.00),

  // Galpão 21
  createElecRecord(1, 'Galpão 21', 11638, 10473.60, 1, 12.04, 17.00),
  createElecRecord(2, 'Galpão 21', 11400, 10294.78, 1, 14.01, 15.00),
  createElecRecord(3, 'Galpão 21', 13929, 9471.08, 1, 12.30, 17.00),
  createElecRecord(4, 'Galpão 21', 10997, 9872.34, 0, 5.39, 8.00),
  createElecRecord(5, 'Galpão 21', 100, 0, 1194.2, 7149.85, 10401.00),
  createElecRecord(6, 'Galpão 21', 100, 1090.90, 1221.46, 7832.48, 11487.00),
  createElecRecord(7, 'Galpão 21', 100, 1829.25, 184, 6868.46, 10084.00),
  createElecRecord(8, 'Galpão 21', 100, 1318.41, 53, 6502.64, 9556.00),
  createElecRecord(9, 'Galpão 21', 100, 1795.76, 95.45, 5976.66, 8760.00),
  createElecRecord(10, 'Galpão 21', 100, 2225.77, 1185.17, 6449.42, 11007.00),
];

const createWaterRecord = (month: number, unit: string, volume: number, cost: number): WaterRecord => ({
  id: crypto.randomUUID(),
  date: `2024-${month.toString().padStart(2, '0')}-01`,
  unit,
  volume,
  cost
});

export const INITIAL_WATER: WaterRecord[] = [
  createWaterRecord(1, 'Galpão 6', 38, 2673.14),
  createWaterRecord(2, 'Galpão 6', 59, 4782.97),
  createWaterRecord(3, 'Galpão 6', 50, 4166.65),
  createWaterRecord(4, 'Galpão 6', 35, 2660.75),
  createWaterRecord(5, 'Galpão 6', 35, 2748.02),
  createWaterRecord(6, 'Galpão 6', 30, 2234.40),
  createWaterRecord(7, 'Galpão 6', 32, 2490.21),
  createWaterRecord(8, 'Galpão 6', 20, 1612.89),
  createWaterRecord(9, 'Galpão 6', 49, 4166.65),
  createWaterRecord(10, 'Galpão 6', 340, 33647.29),
  createWaterRecord(11, 'Galpão 6', 24, 1978.59),
  createWaterRecord(1, 'Galpão 7', 2, 368.70),
  createWaterRecord(2, 'Galpão 7', 8, 548.85),
  createWaterRecord(3, 'Galpão 7', 10, 811.65),
  createWaterRecord(4, 'Galpão 7', 13, 998.31),
  createWaterRecord(5, 'Galpão 7', 48, 4063.93),
  createWaterRecord(6, 'Galpão 7', 13, 998.31),
  createWaterRecord(7, 'Galpão 7', 14, 1122.75),
  createWaterRecord(8, 'Galpão 7', 12, 1060.53),
  createWaterRecord(9, 'Galpão 7', 14, 1122.75),
  createWaterRecord(10, 'Galpão 7', 17, 1060.53),
  createWaterRecord(11, 'Galpão 7', 15, 1320.33),
  createWaterRecord(1, 'Galpão 20', 38, 2673.14),
  createWaterRecord(2, 'Galpão 20', 33, 2234.40),
  createWaterRecord(3, 'Galpão 20', 32, 2404.94),
  createWaterRecord(4, 'Galpão 20', 33, 2490.21),
  createWaterRecord(5, 'Galpão 20', 30, 2319.67),
  createWaterRecord(6, 'Galpão 20', 36, 2746.02),
  createWaterRecord(7, 'Galpão 20', 32, 2490.21),
  createWaterRecord(8, 'Galpão 20', 32, 2575.48),
  createWaterRecord(9, 'Galpão 20', 34, 2660.75),
  createWaterRecord(10, 'Galpão 20', 31, 2063.86),
  createWaterRecord(11, 'Galpão 20', 25, 2063.86),
  createWaterRecord(1, 'Galpão 21', 33, 2270.79),
  createWaterRecord(2, 'Galpão 21', 35, 2404.94),
  createWaterRecord(3, 'Galpão 21', 33, 2490.21),
  createWaterRecord(4, 'Galpão 21', 33, 2490.21),
  createWaterRecord(5, 'Galpão 21', 32, 2490.21),
  createWaterRecord(6, 'Galpão 21', 37, 2831.29),
  createWaterRecord(7, 'Galpão 21', 37, 2934.01),
  createWaterRecord(8, 'Galpão 21', 34, 2746.02),
  createWaterRecord(9, 'Galpão 21', 34, 2660.75),
  createWaterRecord(10, 'Galpão 21', 33, 2234.40),
  createWaterRecord(11, 'Galpão 21', 31, 2575.48),
];

const createWaste = (month: number, type: string, category: 'Reciclável' | 'Não Reciclável', weight: number, financial: number): WasteRecord => ({
  id: crypto.randomUUID(),
  date: `2025-${month.toString().padStart(2, '0')}-01`,
  type,
  category,
  weight,
  financial,
  pricePerKg: weight > 0 ? financial / weight : 0
});

export const INITIAL_WASTE: WasteRecord[] = [
  // Janeiro 2025
  createWaste(1, 'Papelão (Caixas)', 'Reciclável', 1663, 798.24),
  createWaste(1, 'Plastico', 'Reciclável', 430, 206.40),
  createWaste(1, 'Madeira', 'Reciclável', 190, 0),
  createWaste(1, 'Eletronica', 'Reciclável', 0, 0),
  createWaste(1, 'Ferro', 'Reciclável', 1280, 768.00),
  createWaste(1, 'Não Reciclaveis', 'Não Reciclável', 57, 0),

  // Fevereiro 2025
  createWaste(2, 'Papelão (Caixas)', 'Reciclável', 929, 445.92),
  createWaste(2, 'Plastico', 'Reciclável', 432, 207.36),
  createWaste(2, 'Madeira', 'Reciclável', 554, 0),
  createWaste(2, 'Eletronica', 'Reciclável', 20, 9.60),
  createWaste(2, 'Ferro', 'Reciclável', 130, 62.40),
  createWaste(2, 'Não Reciclaveis', 'Não Reciclável', 154, 0),

  // Março 2025
  createWaste(3, 'Papelão (Caixas)', 'Reciclável', 2232, 1071.36),
  createWaste(3, 'Plastico', 'Reciclável', 580, 278.40),
  createWaste(3, 'Madeira', 'Reciclável', 62, 0),
  createWaste(3, 'Eletronica', 'Reciclável', 0, 0),
  createWaste(3, 'Ferro', 'Reciclável', 230, 110.40),
  createWaste(3, 'Não Reciclaveis', 'Não Reciclável', 326, 0),

  // Abril 2025
  createWaste(4, 'Papelão (Caixas)', 'Reciclável', 678, 325.44),
  createWaste(4, 'Plastico', 'Reciclável', 340, 163.20),
  createWaste(4, 'Madeira', 'Reciclável', 420, 0),
  createWaste(4, 'Eletronica', 'Reciclável', 90, 43.20),
  createWaste(4, 'Ferro', 'Reciclável', 120, 57.60),
  createWaste(4, 'Não Reciclaveis', 'Não Reciclável', 162, 0),

  // Maio 2025
  createWaste(5, 'Papelão (Caixas)', 'Reciclável', 5203, 2497.44),
  createWaste(5, 'Plastico', 'Reciclável', 831, 398.88),
  createWaste(5, 'Madeira', 'Reciclável', 950, 0),
  createWaste(5, 'Eletronica', 'Reciclável', 40, 19.20),
  createWaste(5, 'Ferro', 'Reciclável', 50, 24.00),
  createWaste(5, 'Não Reciclaveis', 'Não Reciclável', 425, 0),

  // Junho 2025
  createWaste(6, 'Papelão (Caixas)', 'Reciclável', 2825, 1356.00),
  createWaste(6, 'Plastico', 'Reciclável', 682, 327.36),
  createWaste(6, 'Madeira', 'Reciclável', 124, 0),
  createWaste(6, 'Eletronica', 'Reciclável', 0, 0),
  createWaste(6, 'Ferro', 'Reciclável', 135, 64.80),
  createWaste(6, 'Não Reciclaveis', 'Não Reciclável', 214, 0),

  // Julho 2025
  createWaste(7, 'Papelão (Caixas)', 'Reciclável', 3850, 1848.00),
  createWaste(7, 'Plastico', 'Reciclável', 355, 170.40),
  createWaste(7, 'Madeira', 'Reciclável', 1577, 0),
  createWaste(7, 'Eletronica', 'Reciclável', 11, 5.28),
  createWaste(7, 'Ferro', 'Reciclável', 240, 115.20),
  createWaste(7, 'Não Reciclaveis', 'Não Reciclável', 307, 0),

  // Agosto 2025
  createWaste(8, 'Papelão (Caixas)', 'Reciclável', 4190, 2011.20),
  createWaste(8, 'Plastico', 'Reciclável', 941, 451.68),
  createWaste(8, 'Madeira', 'Reciclável', 217, 0),
  createWaste(8, 'Eletronica', 'Reciclável', 80, 38.40),
  createWaste(8, 'Ferro', 'Reciclável', 180, 86.40),
  createWaste(8, 'Não Reciclaveis', 'Não Reciclável', 312, 0),

  // Setembro 2025
  createWaste(9, 'Papelão (Caixas)', 'Reciclável', 6890, 3307.20),
  createWaste(9, 'Plastico', 'Reciclável', 972, 466.56),
  createWaste(9, 'Madeira', 'Reciclável', 373, 0),
  createWaste(9, 'Eletronica', 'Reciclável', 85, 40.80),
  createWaste(9, 'Ferro', 'Reciclável', 190, 91.20),
  createWaste(9, 'Não Reciclaveis', 'Não Reciclável', 310, 0),

  // Outubro 2025
  createWaste(10, 'Papelão (Caixas)', 'Reciclável', 4299, 2149.50),
  createWaste(10, 'Plastico', 'Reciclável', 830, 415.00),
  createWaste(10, 'Madeira', 'Reciclável', 342, 0),
  createWaste(10, 'Eletronica', 'Reciclável', 0, 0),
  createWaste(10, 'Ferro', 'Reciclável', 120, 60.00),
  createWaste(10, 'Não Reciclaveis', 'Não Reciclável', 189, 0),
];