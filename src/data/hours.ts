export type DaySchedule = {
  day: string;
  shortDay: string;
  closed: boolean;
  slots?: { open: string; close: string }[];
};

export const hours: DaySchedule[] = [
  { day: 'Lundi',    shortDay: 'Lun', closed: true },
  { day: 'Mardi',    shortDay: 'Mar', closed: false, slots: [{ open: '08:00', close: '12:30' }, { open: '15:30', close: '19:00' }] },
  { day: 'Mercredi', shortDay: 'Mer', closed: false, slots: [{ open: '08:00', close: '12:30' }, { open: '15:30', close: '19:00' }] },
  { day: 'Jeudi',    shortDay: 'Jeu', closed: false, slots: [{ open: '08:00', close: '12:30' }, { open: '15:30', close: '19:00' }] },
  { day: 'Vendredi', shortDay: 'Ven', closed: false, slots: [{ open: '08:00', close: '12:30' }, { open: '15:30', close: '19:00' }] },
  { day: 'Samedi',   shortDay: 'Sam', closed: false, slots: [{ open: '08:00', close: '12:30' }, { open: '15:30', close: '19:00' }] },
  { day: 'Dimanche', shortDay: 'Dim', closed: false, slots: [{ open: '09:00', close: '12:30' }] },
];

// For Schema.org openingHours
export const openingHoursSchema: string[] = [
  'Tu-Sa 08:00-12:30',
  'Tu-Sa 15:30-19:00',
  'Su 09:00-12:30',
];
