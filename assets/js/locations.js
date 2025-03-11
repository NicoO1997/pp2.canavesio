// Organizar localidades por provincia
const localitiesByProvince = {
    'Buenos Aires': [
        '25 de Mayo', '9 de Julio', 'Adolfo Gonzales Chaves', 'Alberti', 'Arrecifes', 'Avellaneda', 'Ayacucho', 'Azul', 'Bahía Blanca', 'Balcarce', 
        'Baradero', 'Benito Juárez', 'Berazategui', 'Berisso', 'Bolívar', 'Bragado', 'Brandsen', 'Campana', 'Cañuelas', 'Capitán Sarmiento', 
        'Carlos Casares', 'Carlos Tejedor', 'Carmen de Areco', 'Castelli', 'Chacabuco', 'Chascomús', 'Chivilcoy', 'Colón', 'Coronel Dorrego', 
        'Coronel Pringles', 'Coronel Rosales', 'Coronel Suárez', 'Daireaux', 'Dolores', 'Ensenada', 'Escobar', 'Esteban Echeverría', 'Exaltación de la Cruz', 
        'Ezeiza', 'Florencio Varela', 'Florentino Ameghino', 'General Alvarado', 'General Alvear', 'General Arenales', 'General Belgrano', 
        'General Guido', 'General La Madrid', 'General Las Heras', 'General Lavalle', 'General Madariaga', 'General Paz', 'General Pinto', 
        'General Pueyrredón', 'General Rodríguez', 'General San Martín', 'General Viamonte', 'General Villegas', 'Guaminí', 'Hipólito Yrigoyen', 
        'Hurlingham', 'Ituzaingó', 'José C. Paz', 'Junín', 'La Costa', 'La Matanza', 'La Plata', 'Lanús', 'Laprida', 'Las Flores', 'Leandro N. Alem', 
        'Lincoln', 'Lobería', 'Lobos', 'Lomas de Zamora', 'Luján', 'Magdalena', 'Maipú', 'Malvinas Argentinas', 'Mar Chiquita', 'Mar del Plata', 
        'Marcos Paz', 'Mercedes', 'Merlo', 'Monte', 'Monte Hermoso', 'Moreno', 'Morón', 'Navarro', 'Necochea', 'Olavarría', 'Patagones', 
        'Pehuajó', 'Pellegrini', 'Pergamino', 'Pila', 'Pilar', 'Pinamar', 'Presidente Perón', 'Puan', 'Punta Indio', 'Quilmes', 'Ramallo', 
        'Rauch', 'Rivadavia', 'Rojas', 'Roque Pérez', 'Saavedra', 'Saladillo', 'Salliqueló', 'Salto', 'San Andrés de Giles', 'San Antonio de Areco', 
        'San Cayetano', 'San Fernando', 'San Isidro', 'San Miguel', 'San Nicolás de los Arroyos', 'San Pedro', 'San Vicente', 'Suipacha', 'Tandil', 
        'Tapalqué', 'Tigre', 'Tordillo', 'Tornquist', 'Trenque Lauquen', 'Tres Arroyos', 'Tres de Febrero', 'Tres Lomas', 'Vicente López', 
        'Villa Gesell', 'Villarino', 'Zárate'
    ],
    'Catamarca': [
        'Ancasti', 'Andalgalá', 'Antofagasta de la Sierra', 'Belén', 'Capayán', 'Capital', 'El Alto', 'Fray Mamerto Esquiú', 'La Paz', 
        'Paclín', 'Pomán', 'Santa María', 'Santa Rosa', 'Tinogasta', 'Valle Viejo'
    ],
    'Chaco': [
        'Almirante Brown', 'Bermejo', 'Comandante Fernández', 'Chacabuco', 'Doce de Octubre', 'Dos de Abril', 'Fray Justo Santa María de Oro', 
        'General Belgrano', 'General Donovan', 'General Güemes', 'Independencia', 'Libertad', 'Libertador General San Martín', 'Maipú', 'Mayor Luis J. Fontana', 
        'Nueve de Julio', 'O\'Higgins', 'Presidencia de la Plaza', 'Primero de Mayo', 'Quitilipi', 'Resistencia', 'San Fernando', 'San Lorenzo', 
        'Sargento Cabral', 'Tapenagá', 'Veinticinco de Mayo', '2 de Abril'
    ],
    'Chubut': [
        'Biedma', 'Cushamen', 'Escalante', 'Florentino Ameghino', 'Futaleufú', 'Gaiman', 'Gastre', 'Languiñeo', 'Mártires', 'Paso de Indios', 
        'Rawson', 'Río Senguer', 'Sarmiento', 'Tehuelches', 'Telsen', 'Comodoro Rivadavia', 'Puerto Madryn', 'Trelew', 'Esquel'
    ],
    'Córdoba': [
        'Calamuchita', 'Capital', 'Colón', 'Cruz del Eje', 'General Roca', 'General San Martín', 'Ischilín', 'Juárez Celman', 'Marcos Juárez', 
        'Minas', 'Pocho', 'Presidente Roque Sáenz Peña', 'Punilla', 'Río Cuarto', 'Río Primero', 'Río Seco', 'Río Segundo', 'San Alberto', 
        'San Javier', 'San Justo', 'Santa María', 'Sobremonte', 'Tercero Arriba', 'Totoral', 'Tulumba', 'Unión', 'Córdoba', 'Río Cuarto', 
        'Villa María', 'San Francisco', 'Alta Gracia', 'Río Tercero', 'Bell Ville', 'Villa Carlos Paz', 'Jesús María', 'Marcos Juárez', 
        'Cruz del Eje', 'Arroyito', 'La Calera', 'Río Segundo', 'Villa Dolores', 'Morteros', 'Cosquín', 'Deán Funes', 'Laboulaye', 
        'Corral de Bustos', 'Villa Allende', 'General Deheza', 'Río Ceballos', 'Oncativo', 'Oliva', 'Colonia Caroya', 'La Falda', 
        'Las Varillas', 'Villa Giardino', 'Leones'
    ],
    'Corrientes': [
        'Bella Vista', 'Berón de Astrada', 'Capital', 'Concepción', 'Curuzú Cuatiá', 'Empedrado', 'Esquina', 'General Alvear', 'General Paz', 
        'Goya', 'Itatí', 'Ituzaingó', 'Lavalle', 'Mburucuyá', 'Mercedes', 'Monte Caseros', 'Paso de los Libres', 'Saladas', 'San Cosme', 
        'San Luis del Palmar', 'San Martín', 'San Miguel', 'San Roque', 'Santo Tomé', 'Sauce'
    ],
    'Entre Ríos': [
        'Colón', 'Concordia', 'Diamante', 'Federación', 'Federal', 'Feliciano', 'Gualeguay', 'Gualeguaychú', 'Islas del Ibicuy', 'La Paz', 
        'Nogoyá', 'Paraná', 'San Salvador', 'Tala', 'Uruguay', 'Victoria', 'Villaguay', 'Chajarí', 'Crespo', 'San José', 'Santa Elena', 
        'Villa Elisa', 'Urdinarrain', 'Concepción del Uruguay', 'Rosario del Tala'
    ],
    'Formosa': [
        'Bermejo', 'Formosa', 'Laishi', 'Matacos', 'Patiño', 'Pilagás', 'Pilcomayo', 'Pirané', 'Ramón Lista'
    ],
    'Jujuy': [
        'Cochinoca', 'El Carmen', 'Dr. Manuel Belgrano', 'Humahuaca', 'Ledesma', 'Palpalá', 'Rinconada', 'San Antonio', 'San Pedro', 
        'Santa Bárbara', 'Santa Catalina', 'Susques', 'Tilcara', 'Tumbaya', 'Valle Grande', 'Yavi'
    ],
    'La Pampa': [
        'Atreucó', 'Caleu Caleu', 'Capital', 'Catrilo', 'Chalileo', 'Chapaleufú', 'Chical Co', 'Conhelo', 'Curacó', 'Guatraché', 'Hucal', 
        'Lihuel Calel', 'Limay Mahuida', 'Loventué', 'Maracó', 'Puelén', 'Quemú Quemú', 'Rancul', 'Realicó', 'Toay', 'Trenel', 'Utracán'
    ],
    'La Rioja': [
        'Arauco', 'Capital', 'Castro Barros', 'Chamical', 'Chilecito', 'Coronel Felipe Varela', 'Famatina', 'General Ángel V. Peñaloza', 
        'General Belgrano', 'General Juan Facundo Quiroga', 'General Lamadrid', 'General Ocampo', 'General San Martín', 'Independencia', 
        'Rosario Vera Peñaloza', 'San Blas de los Sauces', 'Sanagasta', 'Vinchina'
    ],
    'Mendoza': [
        'Capital', 'General Alvear', 'Godoy Cruz', 'Guaymallén', 'Junín', 'La Paz', 'Las Heras', 'Lavalle', 'Luján de Cuyo', 'Maipú', 
        'Malargüe', 'Rivadavia', 'San Carlos', 'San Martín', 'San Rafael', 'Santa Rosa', 'Tunuyán', 'Tupungato'
    ],
    'Misiones': [
        'Apóstoles', 'Cainguás', 'Candelaria', 'Capital', 'Concepción', 'Eldorado', 'General Manuel Belgrano', 'Guaraní', 'Iguazú', 
        'Leandro N. Alem', 'Libertador General San Martín', 'Montecarlo', 'Oberá', 'San Ignacio', 'San Javier', 'San Pedro', '25 de Mayo'
    ],
    'Neuquén': [
        'Aluminé', 'Añelo', 'Catán Lil', 'Chos Malal', 'Collón Curá', 'Confluencia', 'Huiliches', 'Lácar', 'Loncopué', 'Los Lagos', 
        'Minas', 'Ñorquín', 'Pehuenches', 'Picún Leufú', 'Picunches', 'Zapala'
    ],
    'Río Negro': [
        'Adolfo Alsina', 'Avellaneda', 'Bariloche', 'Conesa', 'El Cuy', 'General Roca', '9 de Julio', 'Ñorquincó', 'Pichi Mahuida', 
        'Pilcaniyeu', 'San Antonio', 'Valcheta', '25 de Mayo'
    ],
    'Salta': [
        'Anta', 'Cachi', 'Cafayate', 'Capital', 'Cerrillos', 'Chicoana', 'General Güemes', 'General José de San Martín', 'Guachipas', 
        'Iruya', 'La Caldera', 'La Candelaria', 'La Poma', 'La Viña', 'Los Andes', 'Metán', 'Molinos', 'Orán', 'Rivadavia', 'Rosario de la Frontera', 
        'Rosario de Lerma', 'San Carlos', 'Santa Victoria'
    ],
    'San Juan': [
        'Albardón', 'Angaco', 'Calingasta', 'Capital', 'Caucete', 'Chimbas', 'Iglesia', 'Jáchal', '9 de Julio', 'Pocito', 'Rawson', 'Rivadavia', 
        'San Martín', 'Santa Lucía', 'Sarmiento', 'Ullum', 'Valle Fértil', '25 de Mayo', 'Zonda'
    ],
    'San Luis': [
        'Ayacucho', 'Belgrano', 'Coronel Pringles', 'Chacabuco', 'General Pedernera', 'Gobernador Dupuy', 'Junín', 'La Capital', 'Libertador General San Martín'
    ],
    'Santa Cruz': [
        'Corpen Aike', 'Deseado', 'Güer Aike', 'Lago Argentino', 'Lago Buenos Aires', 'Magallanes', 'Río Chico'
    ],
    'Santa Fe': [
        'Rosario', 'Santa Fe', 'Rafaela', 'Venado Tuerto', 'Reconquista', 
        'San Lorenzo', 'Villa Constitución', 'Casilda', 'Cañada de Gómez', 
        'San Justo', 'Esperanza', 'Sunchales', 'San Jorge', 'Las Parejas', 
        'Tostado', 'San Cristóbal', 'Gálvez', 'Arroyo Seco', 'Coronda', 
        'Firmat', 'El Trébol', 'Hughes', 'Wheelwright', 'Murphy', 'Pérez', 
        'Totoras', 'Granadero Baigorria', 'Santo Tomé', 'Armstrong', 'Ceres', 
        'Funes', 'Rufino', 'Villa Ocampo', 'Carcarañá', 'Sastre', 'San Carlos Centro',
        'Capitán Bermúdez', 'Villa Cañás', 'Frontera', 'Recreo', 'Roldán', 
        'San Genaro', 'San Javier', 'Helvecia', 'Vera', 'Gobernador Crespo', 
        'Villa Gobernador Gálvez', 'Fray Luis Beltrán', 'Santo Domingo', 'San Jerónimo Norte',
        'Calchaquí', 'Laguna Paiva', 'Arroyo Leyes', 'Puerto General San Martín', 'Zavalla',
        'Santa Teresa', 'Sauce Viejo', 'Monte Vera', 'Alejandra'
    ],
    'Santiago del Estero': [
        'Santiago del Estero', 'La Banda', 'Termas de Río Hondo', 'Frías', 
        'Añatuya', 'Quimilí', 'Fernández', 'Monte Quemado', 'Clodomira', 
        'Villa Ojo de Agua', 'Suncho Corral', 'Loreto', 'Campo Gallo', 
        'Pampa de los Guanacos', 'Bandera', 'Nueva Esperanza', 'Villa Atamisqui', 
        'Pinto', 'Colonia Dora', 'Tintina'
    ],
    'Tierra del Fuego': [
        'Ushuaia', 'Río Grande', 'Tolhuin'
    ],
    'Tucumán': [
        'San Miguel de Tucumán', 'Yerba Buena', 'Tafí Viejo', 'Banda del Río Salí', 
        'Concepción', 'Aguilares', 'Monteros', 'Famaillá', 'Lules', 'Las Talitas', 
        'San Isidro de Lules', 'Bella Vista', 'Alderetes', 'Simoca', 'Juan Bautista Alberdi', 
        'Tafí del Valle', 'La Cocha', 'Trancas', 'Graneros', 'Burruyacú'
    ],
    'Mendoza': [
        'Mendoza', 'Godoy Cruz', 'San Rafael', 'Guaymallén', 'Las Heras', 'Maipú', 
        'Luján de Cuyo', 'General Alvear', 'Tunuyán', 'Rivadavia', 'Malargüe', 
        'San Martín', 'Junín', 'La Paz', 'Santa Rosa', 'Tupungato', 'San Carlos', 'Lavalle'
    ]
};

// Lista de todas las provincias
const provinciasList = Object.keys(localitiesByProvince);

// Lista plana de todas las localidades para compatibilidad con código existente
let locationsList = [];
for (const province in localitiesByProvince) {
    locationsList = locationsList.concat(localitiesByProvince[province]);
}

// Ordenar alfabéticamente
locationsList.sort();
provinciasList.sort();

// Cargar datos guardados desde localStorage si existen
const savedLocationsByProvince = localStorage.getItem('localitiesByProvince');
if (savedLocationsByProvince) {
    const savedData = JSON.parse(savedLocationsByProvince);
    // Agregar las nuevas localidades al objeto existente
    for (const province in savedData) {
        if (localitiesByProvince[province]) {
            // Combinar y eliminar duplicados
            const combinedLocalities = [...new Set([...localitiesByProvince[province], ...savedData[province]])];
            localitiesByProvince[province] = combinedLocalities.sort();
        } else {
            // Nueva provincia
            localitiesByProvince[province] = savedData[province].sort();
        }
    }
    
    // Reconstruir la lista de provincias
    Object.keys(localitiesByProvince).sort();
    
    // Reconstruir la lista plana
    locationsList = [];
    for (const province in localitiesByProvince) {
        locationsList = locationsList.concat(localitiesByProvince[province]);
    }
    locationsList.sort();
}