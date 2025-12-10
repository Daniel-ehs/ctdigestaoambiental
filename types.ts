
export type UserRole = 'manager' | 'viewer';
export type Unit = string; 
export const UNITS: string[] = ['Galpão 6', 'Galpão 7', 'Galpão 20', 'Galpão 21'];

export interface User {
  id: string;
  name: string;
  email: string;
  password?: string; // In a real app, this would be hashed or handled by backend
  role: UserRole;
  allowedUnits?: string[]; // New field for access control
}

export interface ElectricityRecord {
  id: string;
  date: string; // ISO YYYY-MM-DD
  unit: string;
  cpflKwh: number;
  cpflCost: number;
  floraKwh: number;
  floraCost: number;
  floraSavings: number;
}

export interface WaterRecord {
  id: string;
  date: string;
  unit: string;
  volume: number; // m3
  cost: number;
}

export interface WasteRecord {
  id: string;
  date: string;
  type: string;
  category: 'Reciclável' | 'Não Reciclável';
  weight: number; // kg
  financial: number;
  pricePerKg?: number;
}

export interface AppState {
  electricity: ElectricityRecord[];
  water: WaterRecord[];
  waste: WasteRecord[];
  units: string[];
  electricityGoal: number;
  waterGoal: number;
  wasteGoal: number;
  users: User[];
  currentUser: User | null;
}

export type ModuleType = 'electricity' | 'water' | 'waste' | 'import' | 'settings';