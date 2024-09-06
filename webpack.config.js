const builder = require('solidie-materials/builders/webpack');

module.exports = builder([
	{
		dest_path: './dist',
		src_files: {
			'license': './components/views/license/license.jsx',
			'login': './components/views/login/login.jsx',
		}
	}
]);
